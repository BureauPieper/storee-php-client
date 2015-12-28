<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;

class ContentTags extends AbstractRequest
{
    function __construct($ctype = null) {
        if (!in_array($ctype, Client::$contentTypes)) {
            throw new Client\ClientException('Invalid content type passed', Client\ClientException::CODE_INVALID_CONTENTYPE);
        }
        $args = [];
        $args['ctype'] = $ctype;
        parent::__construct($args);
    }

    function getPath() {
        return '/content/tags';
    }
}