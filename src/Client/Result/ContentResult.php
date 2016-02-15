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
        if (!isset($this['media'][$id])) {
            return null;
        }
        return $this['media'][$id];
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

//    /**
//     * @param $format
//     * @return mixed
//     */
//    function getSrc($format)
//    {
//        return $this['meta']['src'][$format];
//    }

    /**
     * 1) Preferred images will be set server-side if images are part of the media collection.
     *
     * 2) If not and placeholders are found, one will be returned regardless of the orientation.
     * Returning placeholders based on preference would mean no randomness between the image shown
     * and could return in content-listing with every single one showing the same image.
     * So the parameters have no effect on placeholders. They are just a fallback.
     *
     * @param string $orientation           portrait|landscape
     * @param null $src                     Return the src string instead of the medium array
     *
     * @return null|array
     */
    function getPreferredImage($orientation, $src = null)
    {
        if (!$this->getFilteredMedia(['image'], false)) {
            if ($ph = $this->getPlaceholder()) {
                if ($src) {
                    return $ph['meta']['src'][$src];
                }
                return $ph;
            }
            return null;
        }

        $id = $this['meta']['media']['preferred_orientation'][$orientation];
        foreach($this['media'] as $medium) {
            if ($medium['id'] == $id) {
                if ($src) {
                    return $medium['meta']['src'][$src];
                }
                return $medium;
            }
        }
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
     * If a placeholder pool is set for the content-type a random one will be chosen, unless a specified
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