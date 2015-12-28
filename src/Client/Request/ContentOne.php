<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;

class ContentOne extends AbstractRequest
{
    function __construct(array $args = [], $ctype) {
        if (!in_array($ctype, Client::$contentTypes)) {
            throw new Client\ClientException('Invalid content type passed', Client\ClientException::CODE_INVALID_CONTENTYPE);
        }
        $args['ctype'] = $ctype;
        parent::__construct($args);
    }

    function getPath() {
        return '/content/one';
    }

    function handleResponse($response) {
        if (!$response['result']) {
            return null;
        }
        return new Client\Result\ContentResult($response['result']);
    }
}