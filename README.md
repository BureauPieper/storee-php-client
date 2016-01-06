Store-E PHP Client [![Build Status](https://api.travis-ci.org/BureauPieper/storee-php-client.svg?branch=master)](https://travis-ci.org/BureauPieper/storee-php-client)
=======

[Store-E](http://store-e.nl) is a content(-as-a-service/repository) platform taking care of all your content management needs.
Content is delivered through 'Hotspots' and 'Pages' which you can define. Content comes along with every 
form of media, which is also delivered through-, automated and managed by Store-E. Bitmaps in multiple formats and
resolutions(No restrictions!), videos in H.264, MP4, OGV, documents and much more. 

The client enables you to connect your website, enterprise network or social media platforms to our content repository in a matter of hours.

This library supports [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) logging and provides some extra functionality for [Monolog](https://github.com/Seldaek/monolog). Caching is handled by [Stash](https://github.com/tedious/stash). HTTP abstraction is handled by [Guzzle](https://github.com/guzzle/guzzle). You're free to provide your own for any of the dependencies.

## Installation

### Stand-alone

```bash
$ curl -sS https://getcomposer.org/installer | php
$ composer require bureaupieper/storee-client
$ ./vendor/bin/phpunit
```

### Symfony users

See the [BureauPieper/storee-php-client-bundle](https://github.com/BureauPieper/storee-php-client-bundle) for easy integration!

## Usage:

### Create the configuration container

See the [tree](src/Resources/ConfigTree.php) for all the possibilities. The container makes sure your provided options are conflict-free.

```php
$config = new Config(['apikey' => '1234', 'platform' => 'yourplatform']);
```

### Instantiate the client

```php
$client = new Client($config, \GuzzleHttp\Client $client = null, AbstractDriver $cacheDriver = null, Logger $logger = null);
```

### Create the intermediary request object, and fire away.

```php
$req = Client\Request\Factory::create('profile');
$result = $client->request($req);
```

```php
$req = Client\Request\Factory::create('content/list', [
    'hotspot' => 'my-hotspot'
    'page' => 'my-page',
]);
$result = $client->request($req);
```

Content items in a result set are wrapped by [ContentResult.php](src/Client/Result/ContentResult.php) to ease development.

## HTTP

A plain GuzzleHttp instance is used by default, checkout Guzzle for more information.

- getlastRequest doesnt work when passing your own client, feel free to add ```Bureaupieper\StoreeClientEffectiveUrlMiddleware``` to your own handler stack
when passing a client as following:
```php
$stack = GuzzleHttp\HandlerStack::create();
$effectiveUrlMiddleware = new Bureaupieper\StoreeClientEffectiveUrlMiddleware();
$stack->push(GuzzleHttp\Middleware::mapRequest($effectiveUrlMiddleware));
$client = new GuzzleHttp\Client([
    'handler' => $stack
]);
```

## Caching

Enabled by default with a filesystem driver. See [Stash](https://github.com/tedious/stash) for a wide variety of driver options if you need to setup memcache across multiple nodes for example.

Cache will be refreshed based on the Time-to-renew(ttr) setting, TTL is irrelevant. If the endpoint goes down the platform stays up.

## PSR-3 Logging

Can be enabled, if no implementation is provided Monolog will be used with two RotatingFileHandlers. Both INFO and ERR will be be saved to ``` $config['logs']['default_driver']['path'] ```, but it is recommended to
pass a monolog instance with some filesystem handlers and setup logrorate yourself.

The mail setup will only work with the default logger instance.

## Documentation

W.I.P

## Contact

- piet@store-e.nl (tech)
- info@store-e.nl (inquiries)