(DRAFT)
=======

## Install

composer require bureaupieper/storee-client

## Usage:

### 1.
```php
$config = new Config(['apikey' => '1234', 'platform' => 'your_platform_id']);
$client = new Client(Config $config, \GuzzleHttp\Client $client = null, AbstractDriver $cacheDriver = null, Logger $logger = null);
```

### 2.
```php
$req = Client\Request\Factory::create('profile');
$result = $client->request($req);
```

### 3.
```php
$req = Client\Request\Factory::create('content/list', [
    'hotspot' => 'my-hotspot'
    'page' => 'my-page',
]);
$result = $client->request($req);
```

## HTTP

A plain GuzzleHttp instance is used by default, checkout Guzzle for more information.

## Caching

Enabled by default with a filesystem driver. See Stash for a wide variety of driver options if you need to setup memcache across multiple nodes for example.

Cache will be refresh based on the Time-to-renew(ttr) setting, TTL is irrelevant. If the endpoint goes down the platform stays up.

## PSR-4 Logging

Can be enabled, if no implementation is provided Monolog will be used with two RotatingFileHandlers. Both INFO and ERR will be be saved to ``` $config['logs']['default_driver']['path'] ```, but it is recommended to
pass a monolog instance with some filesystem handlers and setup logrorate yourself.

The mail setup will only work with the default logger instance.