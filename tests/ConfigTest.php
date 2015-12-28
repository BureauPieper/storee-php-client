<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient;

use Bureaupieper\StoreeClient\Client\Config;

/**
 * Making sure the conf tree is correctly setup, evaluating and parsed. Combinations of
 * possibilities are handled by @see InstantationTest
 *
 * Class ConfigTest
 * @package Bureaupieper\StoreeClient\Tests
 * @covers Bureaupieper\StoreeClient\Client\Config
 */
class ConfigTest extends BaseTest
{
    const FQCONFIG = '\Bureaupieper\StoreeClient\Client\Config';

    /**
     * Providers
     */

    function validConf() {
        return [
            'apikey' => 'sugarbombs',
            'version' => 1,
            'platform' => 'platform',
        ];
    }

    function buildData($key, array $values, $call = 'array_merge') {
        $r = [];
        foreach($values as $v) {
            $r[] = [call_user_func($call, $this->validConf(), [$key => $v])];
        }
        return $r;
    }

    function invalidEndpoints() {
        return $this->buildData('endpoint', [false, '', null, 0]);
    }

    function validEndpoints() {
        return $this->buildData('endpoint', ['http://endpoint']);
    }

    function invalidKeys() {
        return $this->buildData('apikey', [false, '', null, 0]);
    }

    function validKeys() {
        return $this->buildData('apikey', ['qewr', 'qwer23qwer', 'qwe@!#$!qewr']);
    }

    function invalidPlatforms() {
        return $this->buildData('platform', [false, '', null, 0, 21341234]);
    }

    function validPlatforms() {
        return $this->buildData('platform', ['qewr']);
    }

    function invalidVersions() {
        return $this->buildData('version', [false, '', null, 0, 'qwer', '123.12']);
    }

    function validVersions() {
        return $this->buildData('version', [1, 11, '1', '11']);
    }

    function invalidFormats() {
        return $this->buildData('format', [false, '', null, 0, 'html']);
    }

    function validFormats() {
        return $this->buildData('format', ['json', 'xml']);
    }

    function invalidLogging() {
        return $this->buildData('logs', [
            1,
            'true',
            ['enabled' => 'true'],
            ['default_driver' => new \stdClass()],
            ['default_driver' => 'true'],
            ['default_driver' => ['enabled' => 1]],
            ['default_driver' => ['enabled' => 'true']],
            ['default_driver' => ['path' => null]],
            ['mail' => true],
            ['mail' => ['enabled' => true]],
            ['mail' => ['to' => 'qwer', 'subject' => 'qwer']],
            ['mail' => ['from' => 'qwer', 'subject' => 'qwer']],
            ['mail' => ['to' => 'qwer', 'from' => 'qwer']],
            ['mail' => ['to' => '', 'from' => '', 'subject' => '']],
        ]);
    }

    function validLogging() {
        return $this->buildData('logs', [
            true,
            null,
            ['enabled' => true],
            ['default_driver' => [
                'enabled' => false
            ]],
            ['default_driver' => false],
            ['default_driver' => null],
            ['default_driver' => [
                'path' => '/bla/bla',
            ]],
            ['default_driver' => ['mail' => ['to' => 'qwer', 'subject' => 'qwer', 'from' => 'qwer']]]
        ]);
    }

    function validLoggingDisabled() {
        return $this->buildData('logs', [
            false,
            ['enabled' => false],
        ]);
    }

    function invalidCaching() {
        return $this->buildData('cache', [
            1,
            'true',
            ['enabled' => 'true'],
            ['ttr' => '60'],
            ['ttr' => 50.10],
            ['default_driver' => new \stdClass()],
            ['default_driver' => 'true'],
            ['default_driver' => ['enabled' => 1]],
            ['default_driver' => ['enabled' => 'true']],
            ['default_driver' => ['path' => null]],
        ]);
    }

    function validCaching() {
        return $this->buildData('cache', [
            true,
            null,
            ['enabled' => true],
            ['ttr' => 60 * 60],
            ['default_driver' => [
                'enabled' => false
            ]],
            ['default_driver' => false],
            ['default_driver' => null],
            ['default_driver' => [
                'path' => '/bla/bla',
            ]],
        ]);
    }

    function validCachingDisabled() {
        return $this->buildData('cache', [
            false,
            ['enabled' => false],
        ]);
    }


    /**
     * Tests
     */

    function testValidConf() {
        $this->assertInstanceOf(self::FQCONFIG, new Config($this->validConf()));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /must be of the type array, none given/
     */
    function testNoConfig() {
        new Config();
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /endpoint/
     * @dataProvider invalidEndpoints
     */
    function testInvalidEndpoint($conf) {
        new Config($conf);
    }

    /**
     * @dataProvider validEndpoints
     */
    function testValidEndpoint($conf) {
        $this->assertInstanceOf(self::FQCONFIG, new Config($conf));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /apikey/
     * @dataProvider invalidKeys
     */
    function testInvalidAPIKey($conf) {
        $conf = new Config($conf);
    }

    /**
     * @dataProvider validKeys
     */
    function testValidAPIKey($conf) {
        $this->assertInstanceOf(self::FQCONFIG, new Config($conf));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /version/
     * @dataProvider invalidVersions
     */
    function testInvalidVersion($conf) {
        new Config($conf);
    }

    /**
     * @dataProvider validVersions
     */
    function testValidVersion($conf) {
        $this->assertInstanceOf(self::FQCONFIG, new Config($conf));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /platform/
     * @dataProvider invalidPlatforms
     */
    function testInvalidPlatform($conf) {
        new Config($conf);
    }

    /**
     * @dataProvider validPlatforms
     */
    function testValidPlatform($conf) {
        $this->assertInstanceOf(self::FQCONFIG, new Config($conf));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /format/
     * @dataProvider invalidFormats
     */
    function testInvalidFormat($conf) {
        new Config($conf);
    }

    /**
     * @dataProvider validFormats
     */
    function testValidFormat($conf) {
        $this->assertInstanceOf(self::FQCONFIG, new Config($conf));
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /logs/
     * @dataProvider invalidLogging
     */
    function testInvalidLoggingEnabled($conf) {
        new Config($conf);
    }

    /**
     * @dataProvider validLogging
     */
    function testValidLoggingEnabled($conf) {
        $conf = new Config($conf);
        $this->assertInstanceOf(self::FQCONFIG, $conf);
        $this->assertTrue($conf['logs']['enabled']);
    }

    /**
     * @dataProvider validLoggingDisabled
     */
    function testValidLoggingDisabled($conf) {
        $conf = new Config($conf);
        $this->assertInstanceOf(self::FQCONFIG, $conf);
        $this->assertFalse($conf['logs']['enabled']);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /cache/
     * @dataProvider invalidCaching
     */
    function testInvalidCachingEnabled($conf) {
        new Config($conf);
    }

    /**
     * @dataProvider validCaching
     */
    function testValidCachingEnabled($conf) {
        $conf = new Config($conf);
        $this->assertInstanceOf(self::FQCONFIG, $conf);
        $this->assertTrue($conf['cache']['enabled']);
    }

    /**
     * @dataProvider validCachingDisabled
     */
    function testValidCachingDisabled($conf) {
        $conf = new Config($conf);
        $this->assertInstanceOf(self::FQCONFIG, $conf);
        $this->assertFalse($conf['cache']['enabled']);
    }
}