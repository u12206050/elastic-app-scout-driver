<p align="center">
    <img width="280px" src="logo.png">
</p>

<p align="center">
    <a href="https://packagist.org/packages/u12206050/elastic-app-scout-driver"><img src="https://poser.pugx.org/u12206050/elastic-app-scout-driver/v/stable"></a>
    <a href="https://packagist.org/packages/u12206050/elastic-app-scout-driver"><img src="https://poser.pugx.org/u12206050/elastic-app-scout-driver/downloads"></a>
    <a href="https://packagist.org/packages/u12206050/elastic-app-scout-driver"><img src="https://poser.pugx.org/u12206050/elastic-app-scout-driver/license"></a>
    <a href="https://paypal.me/day4pay"><img src="https://img.shields.io/badge/donate-paypal-blue"></a>
</p>

---

Elastic App Search driver for Laravel Scout.

## Contents

* [Compatibility](#compatibility)
* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Basic Usage](#basic-usage)

## Compatibility

The current version of Elastic App Scout Driver has been tested with the following configuration:

* PHP 7.2-7.4
* Elasticsearch 7.0-7.6
* Laravel 6.x-7.x
* Laravel Scout 7.x-8.x

## Requirements

* Laravel Scout
* Elasticsearch 7.0-7.6
* App Search 7.0-7.6

## Installation

The library can be installed via Composer:

```bash
composer require u12206050/elastic-app-scout-driver
```

## Configuration

Change the `driver` option in the `config/scout.php` file to `elastic_app`

```bash
php artisan vendor:publish --provider="ElasticAppScoutDriver\ServiceProvider"
```

Add your Elastic App Search url and key to you `.env` file

```
ELASTIC_APP_ENDPOINT=
ELASTIC_APP_KEY=
```

## Basic usage

This driver uses [Elastic App Search](https://github.com/elastic/app-search-php)
Meaning you can have alot more flexible where clauses available [READ MORE](https://swiftype.com/documentation/app-search/api/search/filters)

```php
$orders = App\Order::search('')->where('created_at', [
    'from' => '2020-01-01T12:00:00+00:00',
    'to' => '2020-12-31T12:00:00+00:00'
]);
```

When the query string is omitted, then all records are returned.
```php
$orders = App\Order::search()->where('user_id', 1)->get();
``` 

Please refer to [the official Laravel Scout documentation](https://laravel.com/docs/6.x/scout)
and the [app search api](https://swiftype.com/documentation/app-search/api/search)
for more details and usage examples.


## Maintain

All PRs and RFCs are very welcome.
