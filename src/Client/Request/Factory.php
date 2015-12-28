<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client\Request;


class Factory
{
    /**
     * @param $request
     * @return AbstractRequest
     */
    static function create($request) {
        $cls = __NAMESPACE__ . '\\' . ucfirst($request);
        return new $cls;
    }
}