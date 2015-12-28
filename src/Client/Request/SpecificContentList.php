<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;

class SpecificContentList extends AbstractRequest
{
    private $path;

    function __construct(array $args = [], $type) {
        throw new \Exception('Not yet implemented');
    }

    function getPath() {
        return $this->path;
    }
}