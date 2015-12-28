<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


use Bureaupieper\StoreeClient\Client;

class Profile extends AbstractRequest
{
    function getPath() {
        return '/profile';
    }

    function handleResponse($response) {
        if (!$response['result']) {
            return null;
        }
        return new Client\Result\ProfileResult($response['result']);
    }
}