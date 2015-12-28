<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;
use Bureaupieper\StoreeClient\Client\ClientException;

class ContentCategories extends AbstractRequest
{
    function __construct($ctype) {
        if (!in_array($ctype, Client::$contentTypes)) {
            throw new ClientException('Invalid content type passed', ClientException::CODE_INVALID_CONTENTYPE);
        }
        $args = [];
        $args['ctype'] = $ctype;
        parent::__construct($args);
    }

    function getPath() {
        return '/content/categories';
    }
}