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
use Patoui\Router\Router;
use Patoui\Router\ServerRequest;

$router = new Router();
$homeController = new class {
    public function index()
    {
        echo 'Hello World!';
    }
};

$router->addRoute(new Route('get', '/foobar', $homeController, 'index'));

$router->resolve(ServerRequest::makeWithGlobals());
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
