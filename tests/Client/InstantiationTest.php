<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client;


use Bureaupieper\StoreeClient\Client;
use Bureaupieper\StoreeClient\BaseTest;

/**
 * Class InstantiationTest
 * @package Bureaupieper\StoreeClient\Client
 * @covers Bureaupieper\StoreeClient\Client::__construct
 */
class InstantiationTest extends BaseTest
{
    function validConfig() {
        return [
            'version' => 1,
            'platform' => 'sugarbombs.com',
            'apikey' => 'apikey'
        ];
    }

    function validConfigInstance() {
        return new Client\Config($this->validConfig());
    }

    function testValidInstance() {
        $this->assertInstanceOf('Bureaupieper\\StoreeClient\\Client', new Client($this->validConfigInstance()));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /must be an instance of Bureaupieper\\StoreeClient\\Client\\Config/
     */
    function testInvalidConf() {
        new Client;
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /must be an instance of GuzzleHttp\\Client/
     */
    function testInvalidGuzzle() {
        new Client($this->validConfigInstance(), new \stdClass());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /must be an instance of Stash\\Driver\\AbstractDriver/
     */
    function testInvalidCacheDriver() {
        new Client($this->validConfigInstance(), null, new \stdClass());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /must implement interface Psr\\Log\\LoggerInterface/
     */
    function testInvalidLogger() {
        new Client($this->validConfigInstance(), null, null, new \stdClass());
    }

    /**
     * @expectedException     \Bureaupieper\StoreeClient\Client\ClientException
     * @expectedExceptionCode 1
     */
    function testNoCacheDriver() {
        $conf = $this->validConfig();
        $conf['cache'] = [
            'default_driver' => false
        ];
        new Client(new Client\Config($conf));
    }

    /**
     * @expectedException     \Bureaupieper\StoreeClient\Client\ClientException
     * @expectedExceptionCode 16
     */
    function testPathForDefaultCacheNotWritable() {
        $conf = $this->validConfig();
        $conf['cache'] = [
            'default_driver' => [
                'path' => '/some_strange_path_that_shouldnt_exist',
            ]
        ];
        new Client(new Client\Config($conf));
    }

    /**
     * @expectedException     \Bureaupieper\StoreeClient\Client\ClientException
     * @expectedExceptionCode 2
     */
    function testNoLoggingDriver() {
        $conf = $this->validConfig();
        $conf['logs'] = [
            'default_driver' => false
        ];
        new Client(new Client\Config($conf));
    }

    /**
     * @expectedException     \Bureaupieper\StoreeClient\Client\ClientException
     * @expectedExceptionCode 8
     */
    function testPathForDefaultLoggerNotWritable() {
        $conf = $this->validConfig();
        $conf['logs'] = [
            'default_driver' => [
                'path' => '/some_strange_path_that_shouldnt_exist',
            ]
        ];
        new Client(new Client\Config($conf));
    }
}