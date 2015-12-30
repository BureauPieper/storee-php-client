<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;
use Bureaupieper\StoreeClient\Client\Result\ContentResult;

class ContentGrouped extends AbstractRequest
{
    function __construct(array $args = [], array $types = []) {
        if ($types) {
            foreach ($types as $type) {
                if (!in_array($type, Client::$contentTypes)) {
                    throw new Client\ClientException('Invalid content type passed', Client\ClientException::CODE_INVALID_CONTENTYPE);
                }
            }
            $args['ctypes'] = $types;
        }
        parent::__construct($args);
    }
    function getPath() {
        return '/content/grouped';
    }
    function handleResponse($response) {
        foreach($response['result'] as $k => $v) {
            $response['result'][$k] = new ContentResult($v);
        }
        return $response;
    }
}