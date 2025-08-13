# Laravel Disposable Email

[![Tests](https://github.com/Propaganistas/Laravel-Disposable-Email/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/Propaganistas/Laravel-Disposable-Email/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-disposable-email/v/stable)](https://packagist.org/packages/propaganistas/laravel-disposable-email)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-disposable-email/downloads)](https://packagist.org/packages/propaganistas/laravel-disposable-email)
[![License](https://poser.pugx.org/propaganistas/laravel-disposable-email/license)](https://packagist.org/packages/propaganistas/laravel-disposable-email)

Adds a validator to Laravel for checking whether a given email address isn't originating from disposable email services such as `Mailinator`, `Guerillamail`, ...
Uses the disposable domains blacklist from [disposable/disposable](https://github.com/disposable/disposable) by default.

### Installation

1. Run the Composer require command to install the package. The service provider is discovered automatically.

    ```bash
    composer require propaganistas/laravel-disposable-email
    ```

2. Publish the configuration file and adapt the configuration as desired:

    ```bash
    php artisan vendor:publish --tag=laravel-disposable-email
    ```

3. (optional) In your languages directory, add for each language an extra language line for the validator:

    ```php
    'indisposable' => 'Disposable email addresses are not allowed.',
    ```

4. (optional) This package receives a **weekly** patch release containing updates to the built-in disposable domains list. If you are not able to bump your installed version accordingly, or just want to stay ahead of things, make sure to update the domains list yourself at any interval you like by running or scheduling the `disposable:update` command: 

    ```php
    // routes/console.php
    
    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('disposable:update')->daily();
    ```
### Usage

Use the `indisposable` validator to ensure a given field doesn't hold a disposable email address. You'll probably want to add it after the `email` validator to make sure a valid email is passed through:

```php
'field' => 'email|indisposable',
```

### Custom fetches

By default the package retrieves a new list by using `file_get_contents()`. 
If your application has different needs (e.g. when behind a proxy) please review the `disposable-email.fetcher` configuration value.
