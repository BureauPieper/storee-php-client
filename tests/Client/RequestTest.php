<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient\Client;


use Bureaupieper\StoreeClient\Client;
use Bureaupieper\StoreeClient\Exception;
use Bureaupieper\StoreeClient\BaseTest;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Stash\Driver\Ephemeral;

/**
 * Class RequestTest
 * @package Bureaupieper\StoreeClient\Client
 * @covers Bureaupieper\StoreeClient\Client::request
 */
class RequestTest extends BaseTest
{
    /**
     * @var TestHandler
     */
    private $loghandler;

    function conf() {
        return [
            'apikey' => 'apikey',
            'version' => 1,
            'platform' => 'platform'
        ];
    }

    function prepare(array $responses, array $conf)
    {
        $mock = new MockHandler($responses);
        $guzzle = new \GuzzleHttp\Client(['handler' => HandlerStack::create($mock)]);
        $logger = new Logger('test');
        $this->loghandler = new TestHandler();
        $logger->pushHandler($this->loghandler);
        return new Client(new Config($conf), $guzzle, new Ephemeral(), $logger);
    }

    function testNoCache()
    {
        $client = $this->prepare([
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
        ], $this->conf());

        $req = Client\Request\Factory::create('profile');
        $result = $client->request($req);

        $this->assertFalse($this->loghandler->hasInfoThatContains('Cache enabled'));

        $this->assertInstanceOf('Bureaupieper\StoreeClient\Client\Result\ProfileResult', $result);
    }

    function testWorkingCache()
    {
        $client = $this->prepare([
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
        ], array_merge($this->conf(), ['cache' => true]));

        $req = Client\Request\Factory::create('profile');
        $result = $client->request($req);

        $key = $client->buildCacheKey($req, $client->buildRequestArgs($req));
        $item = $client->getCachepool()->getItem($key);

        $this->assertTrue($this->loghandler->hasInfoThatContains('Cache enabled'));
        $this->assertTrue($this->loghandler->hasInfoThatContains('Cache MISS'));

        $req = Client\Request\Factory::create('profile');
        $result = $client->request($req);

        $this->assertTrue($this->loghandler->hasInfoThatContains('Using cache'));
        $this->assertTrue($this->loghandler->hasInfoThatContains('Saving cache'));
        $this->assertFalse($item->isMiss());
    }

    function testTtr()
    {
        $client = $this->prepare([
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
        ], array_merge($this->conf(), ['cache' => true]));

        $req = Client\Request\Factory::create('profile');
        $result = $client->request($req);

        $this->assertTrue($this->loghandler->hasInfoThatContains('Cache MISS'));

        $req = Client\Request\Factory::create('profile');
        $req->setTtr(0);
        $result = $client->request($req);

        $this->assertTrue($this->loghandler->hasInfoThatContains('Cache MISS'));
        $this->assertTrue($this->loghandler->hasInfoThatContains('Refreshing resource'));
    }

    function testStaleDataOnError()
    {
        $client = $this->prepare([
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
            new Response(500, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
        ], array_merge($this->conf(), ['cache' => true]));

        $req = Client\Request\Factory::create('profile');
        $result = $client->request($req);

        $req = Client\Request\Factory::create('profile');
        $req->setTtr(0);
        $result = $client->request($req);

        $this->assertTrue($this->loghandler->hasInfoThatContains('Returning stale data'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Internal Server Error/
     */
    function testErrorOnNoStaleData()
    {
        $client = $this->prepare([
            new Response(500, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
        ], array_merge($this->conf(), ['cache' => true]));

        $req = Client\Request\Factory::create('profile');
        $client->request($req);
    }

    function testRefreshingExistingCache()
    {
        $client = $this->prepare([
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
            new Response(200, [], file_get_contents(__DIR__ .'/Fixtures/profile.json')),
        ], array_merge($this->conf(), ['cache' => true]));

        $req = Client\Request\Factory::create('profile');
        $result = $client->request(Client\Request\Factory::create('profile'));

        $req = Client\Request\Factory::create('profile');
        $req->setUseCache(false);
        $result = $client->request($req);

        $this->assertTrue($this->loghandler->hasInfoThatContains('Refreshing cache'));
    }
}