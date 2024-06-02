# phputil/flags-webhooks

[![Version](https://poser.pugx.org/phputil/flags-webhooks/v?style=flat-square)](https://packagist.org/packages/phputil/flags-webhooks)
![Build](https://github.com/thiagodp/phputil-flags-webhooks/actions/workflows/ci.yml/badge.svg?style=flat)
[![License](https://poser.pugx.org/phputil/flags-webhooks/license?style=flat-square)](https://packagist.org/packages/phputil/flags-webhooks)
[![PHP](http://poser.pugx.org/phputil/flags-webhooks/require/php)](https://packagist.org/packages/phputil/flags-webhooks)

> Web hooks for the [phputil/flags](https://github.com/thiagodp/phputil-flags)  framework.

## Installation

> PHP 7.4 or later. It uses [Guzzle](https://guzzlephp.org/) for making HTTP requests.

```bash
composer require phputil/flags-webhooks
```

## Usage

```php
require_once 'vendor/autoload.php';

use \phputil\flags\webhooks\WebhookListener;

$listener = new WebhookListener( 'https://example.com' );

// Now let's use it with the framework
$flags = new \phputil\flags\FlagManager();
$flags->getListeners()->add( $listener );

// Notifying
$flags->enable( 'foo' ); // POST request (creation)
$flags->disable( 'foo' ); // PUT request (update)
$flags->remove( 'foo' ); // DELETE request (removal)
```

## Documentation

_Soon_


## License

[MIT](/LICENSE) © [Thiago Delgado Pinto](https://github.com/thiagodp)
