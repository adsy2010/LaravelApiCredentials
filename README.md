# Laravel Api Credentials

[![Build Status](https://travis-ci.com/adsy2010/LaravelStripeWrapper.svg?branch=master)](https://travis-ci.com/adsy2010/LaravelStripeWrapper)

This package is for use in conjunction with any service which uses public and secret api keys.

Optionally, some services have scopes that a key can be added to, the facility for this has been included.

For example, [Stripe](https://stripe.com/docs/api) has two standard keys. A publishable key and a secret key. Additional
secret restricted keys can be created to limit access to their API, so a list of scopes that the key applies to would be
associated.

## Getting started

To get started, add this packages service provider to your providers list in `app.php` or require the package if using
as part of your own package.

```php
    'providers' => [
        ...
        /*
         * Application Service Providers...
         */
        \Adsy2010\LaravelApiCredentials\LaravelApiCredentialServiceProvider::class,
        ... 
    ],
```

Now if using this package directly in Laravel, run a migration directly to set up the credential and scopes tables.

```bash
php artisan migrate
```

Optionally you can publish the service provider beforehand

```bash 
php artisan vendor:publish --provider=Adsy2010\LaravelApiCredentials\LaravelApiCredentialServiceProvider
```

## Usage

To add an api key to the database, provide a key, value, the service and any named scopes you wish to include.

```php
$key = 'PUBLIC';
$value = 'TEST_VALUE_STRING';
$service = 'TEST_SERVICE';
$scopes = [];

$credentials = (new Credential)->store($key, $value, $service, $scopes);
```

The expected scopes array is an array of arrays comprising name and access keys. If the access key is not included, the
default for access will be READ.

In the instance a scope is not provided, a default named scope of "Public" with read and write access will be created.

```php
$scopes = [
    ['name' => 'Charges', 'access' => ScopeAccess::READ],
    ['name' => 'Payments', 'access' => ScopeAccess::WRITE],
    ['name' => 'Customers', 'access' => ScopeAccess::READ_AND_WRITE],
];
```

You decide you need to change providers and move from twitter to instagram so you choose to clean up your old access
code.

Run the following against the required credentials. The delete method will remove any scopes attached.

```php
//Load a credential and call delete. This will remove the credential and any scopes attached.
$credential->delete();
```

You need to use the key in your package or application, simply request the service, scope and access level, and the
decrypted key will be returned, ready for use.

```php
$service = 'Quickbooks';
$scopeName = 'Publishable';

(new Scope)->retrieveCredentialValue($service, $scopeName, ScopeAccess::READ_AND_WRITE);
```
