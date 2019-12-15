## ⚠️ WARNING ⚠️

**This repository is in active development, use at your own risk.**

# Router

[![Build Status](https://img.shields.io/travis/patoui/router/master.svg?style=flat-square)](https://travis-ci.org/patoui/router)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/patoui/router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/patoui/router/?branch=master)
[![StyleCI](https://github.styleci.io/repos/222272762/shield?branch=master)](https://github.styleci.io/repos/222272762)


A simple HTTP router

## Installation

Updated your `composer.json` file with the follow:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/patoui/router"
        }
    ],
    "require": {
        "patoui/router": "dev-master"
    }
}
```

Then run the following command:

```bash
composer update patoui/router
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use Patoui\Router\Route;
use Patoui\Router\RouteNotFoundException;
use Patoui\Router\Router;
use Patoui\Router\ServerRequest;

class HomeController
{
    public function index()
    {
        echo 'hello';
    }
}

$router = new Router();
$router->addRoute(new Route('get', '/foobar', HomeController::class, 'index'));

try {
    [$controller, $method] = $router->resolve(ServerRequest::makeWithGlobals());
    call_user_func([$controller, $method]);
} catch (RouteNotFoundException $notFoundException) {
    http_response_code(404);
} catch (Exception $exception) {
    http_response_code(500);
}
```

#### Usage with PHP-DI

Add PHP-DI via composer

```bash
composer require php-di/php-di
```

Example
```php
<?php

require 'vendor/autoload.php';

use DI\ContainerBuilder;
use Patoui\Router\Route;
use Patoui\Router\RouteNotFoundException;
use Patoui\Router\Router;
use Patoui\Router\ServerRequest;

$containerBuilder = new ContainerBuilder;
$container = $containerBuilder->build();

class Mailer
{
    public function mail($recipient, $content)
    {
        echo "Sent '{$content}' to '{$recipient}'";
    }
}

class UserManager
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function register($email, $password)
    {
        $this->mailer->mail($email, 'Hello and welcome!');
    }
}

class HomeController
{
    public function __construct(UserManager $user_manager)
    {
        $this->user_manager = $user_manager;
    }

    public function index()
    {
        $this->user_manager->register('pat@email.com', 'foobar');
    }
}

$router = new Router();
$router->addRoute(new Route('get', '/foobar', HomeController::class, 'index'));

try {
    [$controller, $method] = $router->resolve(ServerRequest::makeWithGlobals());
    $container->call([$controller, $method]);
} catch (RouteNotFoundException $notFoundException) {
    http_response_code(404);
} catch (Exception $exception) {
    http_response_code(500);
}
```

### Testing

``` bash
vendor/bin/phpunit
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Patrique Ouimet](https://github.com/patoui)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
