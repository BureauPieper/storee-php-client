<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;

class Search extends AbstractRequest
{
    function __construct($query, array $ctypes, array $args = []) {
        $args['ctypes'] = $ctypes;
        if ($query) {
            $args['search'] = $query;
        }
        parent::__construct($args);
    }

    function getPath() {
        return '/content/search';
    }

    function handleResponse($response) {
        foreach($response['result'] as $k => $v) {
            $response['result'][$k] = new Client\Result\ContentResult($v);
        }
        return $response;
    }
}