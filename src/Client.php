<?php

/**
 * (c) Bureau Pieper <piet@bureaupieper.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bureaupieper\StoreeClient;

use Bureaupieper\StoreeClient\Client\Config;
use Bureaupieper\StoreeClient\Client\Request\AbstractRequest;
use Bureaupieper\StoreeClient\Client\ClientException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Stash\Driver\AbstractDriver;
use Stash\Driver\FileSystem;
use Stash\Pool;

class Client
{
    const CONTENT_TYPE_ARTICLE = 'article';
    const CONTENT_TYPE_ELEMENT = 'element';
    const CONTENT_TYPE_EVENT = 'event';
    const CONTENT_TYPE_PRODUCT = 'product';
    const CONTENT_TYPE_PORTFOLIO = 'portfolio';
    const CONTENT_TYPE_SERVICE = 'service';
    const CONTENT_TYPE_JOB = 'job';
    const CONTENT_TYPE_NEWS = 'news';

    static $contentTypes = [
        self::CONTENT_TYPE_ARTICLE,
        self::CONTENT_TYPE_ELEMENT,
        self::CONTENT_TYPE_EVENT,
        self::CONTENT_TYPE_PORTFOLIO,
        self::CONTENT_TYPE_PRODUCT,
        self::CONTENT_TYPE_SERVICE,
        self::CONTENT_TYPE_JOB,
        self::CONTENT_TYPE_NEWS
    ];

    /**
     * @var Pool
     */
    private $cachepool;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzle;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EffectiveUrlMiddleware
     */
    private $effectiveUrlMiddleware;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @param Config $config
     * @param \GuzzleHttp\Client $client
     * @param AbstractDriver $cacheDriver
     * @throws ClientException
     */
    function __construct(Config $config, \GuzzleHttp\Client $client = null, AbstractDriver $cacheDriver = null, LoggerInterface $logger = null)
    {
        $this->cfg = $config;

        if (!$client)
        {
            $stack = \GuzzleHttp\HandlerStack::create();
            $this->effectiveUrlMiddleware = $urlmiddleware = new EffectiveUrlMiddleware();
            $stack->push(\GuzzleHttp\Middleware::mapRequest($urlmiddleware));
            $client = new \GuzzleHttp\Client([
                'handler' => $stack,
                \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true
            ]);
        }
        $this->guzzle = $client;

        if ($config['cache']['enabled'])
        {
            if (!$cacheDriver) {
                if (!$config['cache']['default_driver']['enabled']) {
                    throw new ClientException(ClientException::CODE_CACHE_NO_DRIVER);
                }
                if (!is_writable($config['cache']['default_driver']['path'])) {
                    throw new ClientException(ClientException::CODE_CACHE_PATH_NOT_WRITABLE, 0, [$config['cache']['default_driver']['path']]);
                }
                $cacheDriver = new FileSystem([
                    'path' => $config['cache']['default_driver']['path'] . '/storee-client'
                ]);
            }
            $this->cachepool = new Pool($cacheDriver);
        }

        if ($config['logs']['enabled'] && !$logger)
        {
            if (!$config['logs']['default_driver']['enabled']) {
                throw new ClientException(ClientException::CODE_LOGGER_NO_DRIVER);
            }
            if (!class_exists('\Monolog\Logger')) {
                throw new ClientException(ClientException::CODE_LOGGER_NO_MONOLOG);
            }
            if (!is_writable($config['logs']['default_driver']['path'])) {
                throw new ClientException(ClientException::CODE_LOGGER_PATH_NOT_WRITABLE, 0, [$config['logs']['default_driver']['path']]);
            }
            $logger = new \Monolog\Logger('storee');
            $logger->pushHandler(new \Monolog\Handler\RotatingFileHandler($config['logs']['default_driver']['path'] . '/storee-client.log', 5, LogLevel::INFO));
            $logger->pushHandler(new \Monolog\Handler\RotatingFileHandler($config['logs']['default_driver']['path'] . '/storee-client.err', 5, LogLevel::ERROR));

            if ($config['logs']['default_driver']['mail']['enabled']) {
                $logger->pushHandler(new \Monolog\Handler\NativeMailerHandler(
                    $config['logs']['default_driver']['mail']['to'],
                    $config['logs']['default_driver']['mail']['subject'],
                    $config['logs']['default_driver']['mail']['from'],
                    LogLevel::ERROR
                ));
            }
        }
        $this->logger = $logger ?: new NullLogger();
    }

    function setDebug($switch)
    {
        $this->debug = (bool)$switch;
    }

    function getCachepool()
    {
        return $this->cachepool;
    }

    function buildRequestArgs(AbstractRequest $request)
    {
        return array_merge($request->getArgs(), [
            'key' => $this->cfg->getApikey(),
            'platform' => $this->cfg->getPlatform(),
            'version' => $this->cfg->getVersion()
        ]);
    }

    function buildCacheKey(AbstractRequest $request, array $args)
    {
        // Sort to normalize the cache key
        asort($args);

        return $request->getPath() . '.' . $this->cfg->getFormat() . '_' . http_build_query($args);
    }

    /**
     * @return EffectiveUrlMiddleware
     */
    function getLastRequest()
    {
        return $this->effectiveUrlMiddleware;
    }

    /**
     * @param AbstractRequest $request
     * @return string
     * @throws \Exception
     */
    function request(AbstractRequest $request)
    {
        $this->logger->info($request->getPath());

        $args = $this->buildRequestArgs($request);

        if ($this->cachepool)
        {
            do {
                $cacheKey = $this->buildCacheKey($request, $args);
                $cacheKeyLog = preg_replace('/key=[^&]+/', 'key=*****', $cacheKey);
                $cacheItem = $this->cachepool->getItem($cacheKey);
                $cachedData = $this->_deserialize($this->cfg->getFormat(), $cacheItem->get());

                // Disabled on a request basis?
                if (!$request->isUseCache()) {
                    break;
                }

                $this->logger->info('Cache enabled', ['key' => $cacheKeyLog]);

                $age = null;
                if (!$cacheItem->isMiss()) {
                    $age = time() - $cacheItem->getCreation()->getTimestamp();
                }

                // Time to renew
                $ttr = $request->getTtr() !== null ? $request->getTtr() : $this->cfg->getCacheTtr();

                // Return the cached data if it's not time yet to renew
                $returnCached = !$cacheItem->isMiss() && $age < $ttr;

//                if ($returnCached) {
//                    // Check the latest hashes
//                    $request->validateCache($this, $cachedData);
//                }

                if ($returnCached) {
                    $msg = 'Using cache';
                }
                else {
                    if (!$age) {
                        $msg = 'Cache MISS';
                    }
                    else {
                        $msg = 'NOT using cache';
                    }
                }

                $this->logger->info($msg, [
                    'age' => $age,
                    'ttr' => $ttr,
                    'returnCached' => $returnCached,
                ]);

                if ($returnCached) {
                    return $request->handleResponse($cachedData);
                }

                // @todo lock cache
            }
            while(false);
        }

        try {
            $uri = $this->cfg->getEndpoint() . $request->getPath() . '.' . $this->cfg->getFormat();

            $this->logger->info('Refreshing resource', [
                'uri' => $uri,
                'args' => http_build_query(array_merge($args, ['key' => '*****'])),
            ]);

            $body = $this->guzzle->get($uri, ['query' => $args])->getBody();
        }
        catch(\Exception $e)
        {
            // Log and report
            $this->logger->error((string) $e);

            if ($this->debug) {
                throw $e;
            }

            // Return stale data
            if (isset($cacheItem) && !$cacheItem->isMiss()) {
                $this->logger->info('Returning stale data!');
                return $request->handleResponse($cachedData);
            }
            else {
                // Sad panda
                throw $e;
            }
        }

        $result = $body->getContents();

        $deserialized = $this->_deserialize($this->cfg->getFormat(), $result);

        // Cache might be disabled, but still renew if there was cached data so that the new result gets returned in
        // case caching might be enabled again in a later request.
        if (isset($cacheItem) && ($request->isUseCache() || (!$request->isUseCache() && !$cacheItem->isMiss()))) {
            $this->logger->info($cacheItem->isMiss() ? 'Saving cache' : 'Refreshing cache');
            $cacheItem->set($result, 365 * 60 * 60 * 24); // ttl not relevant
        }

        return $request->handleResponse($deserialized);
    }

    /**
     * @param $format
     * @param $data
     * @return mixed
     */
    private function _deserialize($format, $data)
    {
        switch($format) {
            case 'json':
                return json_decode($data, true);
            break;
            default:
                return $data;
        }
    }
}