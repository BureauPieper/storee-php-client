<?php
 
 /**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Result;

use Bureaupieper\StoreeClient\Client\ClientException;

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
     * Returns the short content if provided or a substring of the normal version
     *
     * @return string
     */
    function getContentShort() {
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
    function getContentSeparated($forceArrayReturn = false, $maxSize = null) {
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
    function getUrl() {
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
    function getFilteredMedia(array $filters)
    {
        $col = [];
        foreach($this['media'] as $medium) {
            if (in_array($medium['type'], $filters)) {
                $col[] = $medium;
            }
        }
        return $col;
    }

    /**
     * @param $format
     * @return mixed
     */
    function getSrc($format) {
        return $this['meta']['src'][$format];
    }

    /**
     * @param string $orientation           portrait|landscape
     * @param null $src                     Return the src string instead of the medium array
     *
     * @return null|array|string
     */
    function getPreferredImage($orientation, $src = null)
    {
        if (!$this->getImages()) {
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
    function getPagesPublishedTo() {
        $pages = [];
        foreach($this['meta']['issue']['hotspots'] as $hotspot) {
            $pages[$hotspot['page']['slug']] = $hotspot['page'];
        }
        return $pages;
    }
}