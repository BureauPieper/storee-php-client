<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client;


class ClientException extends \Bureaupieper\StoreeClient\Exception
{
    const CODE_CACHE_NO_DRIVER = 0x1;
    const CODE_LOGGER_NO_DRIVER = 0x2;
    const CODE_LOGGER_NO_MONOLOG = 0x4;
    const CODE_LOGGER_PATH_NOT_WRITABLE = 0x8;
    const CODE_CACHE_PATH_NOT_WRITABLE = 0x10;

    static $msg = [
        self::CODE_CACHE_NO_DRIVER => 'Cache is enabled, but no driver passed and the default driver is disabled.',
        self::CODE_LOGGER_NO_DRIVER => 'Logging enabled, but no driver passed and the default driver is disabled.',
        self::CODE_LOGGER_NO_MONOLOG => 'Monolog is used as the default logger, but wasn\'t found.',
        self::CODE_LOGGER_PATH_NOT_WRITABLE => 'Path %s is not writable.',
        self::CODE_CACHE_PATH_NOT_WRITABLE => 'Path %s is not writable.'
    ];

    function __construct($msg = "", $code = 0, $params = []) {
        if (is_int($msg)) {
            $code = $msg;
            $msg = self::$msg[$msg];
        }
        if ($params) {
            $msg = vsprintf($msg, $params);
        }
        parent::__construct($msg, $code);
    }
}