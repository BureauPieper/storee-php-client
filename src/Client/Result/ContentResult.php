<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Result;

/**
 * Class ContentResult
 * @package Bureaupieper\StoreeClient\Client\Result
 *
 * @method getMedia
 * @method getTitle
 * @method getContent
 * @method getId
 * @method getSort
 * @method getSlug
 * @method getSubtitle
 */
class ContentResult extends AbstractResult
{
    /**
     * To memmorize which placeholder is selected from the possible pool of multiple images
     *
     * @var int
     */
    private $placeholder_pick;

    /**
     * Returns the short content if provided or a substring of the normal version
     *
     * @return string
     */
    function getContentShort()
    {
        return $this['content_short'] ?: substr(strip_tags($this['content']), 0, 200);
    }

    /**
     * Content can be split by the tag [split] to open more options in the rendering of the layout, such
     * as creating a leader text from the first section.
     *
     * @param bool $forceArrayReturn    Always return array, whether the content can be split or not
     * @param int $maxSize              Remaining pieces will be glued together
     * @return array|null
     */
    function getContentSeparated($forceArrayReturn = false, $maxSize = null)
    {
        if (isset($this['meta']['content']['split'])) {
            $c = $this['meta']['content']['split'];
            if ($maxSize && $maxSize < count($c)) {
                array_splice($c, $maxSize-1, count($c)-1, implode('', array_slice($c, $maxSize-1)));
            }
            return $c;
        }
        if ($forceArrayReturn) {
            return [$this['content']];
        }
    }

    /**
     * Return the URL set in the dashboard if its set
     *
     * @return string|null
     */
    function getUrl()
    {
        return $this['meta']['content']['endpoint'];
    }

    /**
     * Get a medium by ID
     *
     * @param $id
     * @return null|array
     */
    function getMedium($id)
    {
        foreach($this['media'] as $medium) {
            if ($medium['id'] == $id) {
                return $medium;
            }
        }
    }

    /**
     * @return array
     */
    function getImages()
    {
        return $this->getFilteredMedia(['image']);
    }

    /**
     * Filter media by types
     *
     * @param array $filters    image|video|document|audio|other(binary attachments)
     */
    function getFilteredMedia(array $filters, $allowPlaceholder = true)
    {
        $col = [];
        foreach($this['media'] as $medium) {
            if (!isset($hasImage) && $medium['type'] == 'image') {
                $hasImage = true;
            }
            if (in_array($medium['type'], $filters)) {
                $col[] = $medium;
            }
        }

        // Add a placeholder if the image type was requested, and no images were found.
        if (in_array('image', $filters)
            && !isset($hasImage)
            && $allowPlaceholder
            && $this->getPlaceholder()) {
            $col[] = $this->getPlaceholder();
        }

        return $col;
    }

    /**
     * Did the user favourite an image?
     *
     * @return bool
     */
    function hasPreferredImage() {
        return (bool) $this['meta']['media']['preferred_image'];
    }

    /**
     * If there is a preferred image this one will be returned. If not, the first one of the
     * collection will be returned.
     *
     * If an $orientation is passed, @getPreferredImageByOrientation will be called as a fallback, this will allow
     * alot less boilerplate code in sitations where you might want the user' favourite image as the highest priority,
     * but the best fitting alternative to a certain orientation, if not.
     *
     * F.e.: A large header image:
     *
     * getPreferredImage('1080p', 'landscape')          Get the user-preferred image, and return the url to the source
     *                                                  of the '1080' format. Or give me the image that best fits the
     *                                                  'landscape' aspect. And if there is no image found at all, give me
     *                                                  a placeholder if possible.
     *
     * NB: Falls back to placeholders
     *
     * @param null $aspect                              Return the src string instead of the medium array
     * @param string|null $orientation                  portrait|landscape|null
     *
     * @return null|string|array
     */
    function getPreferredImage($aspect = null, $orientation = null)
    {
        // If theres no images, check for placeholders
        if (!$this->getFilteredMedia(['image'], false)) {
            if ($ph = $this->getPlaceholder()) {
                if ($aspect) {
                    return $ph['meta']['src'][$aspect];
                }
                return $ph;
            }
            return null;
        }

        if ($this->hasPreferredImage()) {
            $medium = $this->getMedium($this['meta']['media']['preferred_image']);
        }
        elseif ($orientation) {
            $medium = $this->getPreferredImageByOrientation($orientation);
        }
        else {
            $medium = $this['media'][0];
        }

        if ($aspect) {
            return $medium['meta']['src'][$aspect];
        }
        return $medium;
    }

    /**
     * These images defined server-side based on aspect ratio and dimensions.
     *
     * NB: Falls back to a possible placeholder.
     *
     * @param string $orientation       portrait|landscape
     * @param null $aspect
     *
     * @return array|string|null
     */
    function getPreferredImageByOrientation($orientation, $aspect = null)
    {
        // If theres no images, check for placeholders
        if (!$this->getFilteredMedia(['image'], false)) {
            if ($ph = $this->getPlaceholder()) {
                if ($aspect) {
                    return $ph['meta']['src'][$aspect];
                }
                return $ph;
            }
            return null;
        }

        $medium = $this->getMedium($this['meta']['media']['preferred_orientation'][$orientation]);
        if ($aspect) {
            return $medium['meta']['src'][$aspect];
        }
        return $medium;
    }

    /**
     * Returns an array of unique pages published to, because there can be multiple hotspots on the same page.
     *
     * @return array
     */
    function getPagesPublishedTo()
    {
        $pages = [];
        foreach($this['meta']['issue']['hotspots'] as $hotspot) {
            $pages[$hotspot['page']['slug']] = $hotspot['page'];
        }
        return $pages;
    }

    /**
     * Multiple placeholders can be set for a specific content-type, keep in mind that one will be
     * set for this session. Server-side relating a specific random placeholder as a preferred one is a WIP.
     */
    function getPlaceholder($index = null)
    {
        if (!isset($this['meta']['media']['placeholder'])) {
            return null;
        }
        if (!$this->placeholder_pick) {
            $this->placeholder_pick = rand(0, count($this['meta']['media']['placeholder']) - 1);
        }
        return $this['meta']['media']['placeholder'][$index ?: $this->placeholder_pick];
    }
}