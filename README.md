# Laravel Disposable Email

[![Build Status](https://travis-ci.org/Propaganistas/Laravel-Disposable-Email.svg?branch=master)](https://travis-ci.org/Propaganistas/Laravel-Disposable-Email)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Disposable-Email/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Disposable-Email/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Disposable-Email/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Propaganistas/Laravel-Disposable-Email/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-disposable-email/v/stable)](https://packagist.org/packages/propaganistas/laravel-disposable-email)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-disposable-email/downloads)](https://packagist.org/packages/propaganistas/laravel-disposable-email)
[![License](https://poser.pugx.org/propaganistas/laravel-disposable-email/license)](https://packagist.org/packages/propaganistas/laravel-disposable-email)

Adds a validator to Laravel for checking whether a given email address isn't originating from disposable email services such as `Mailinator`, `Guerillamail`, ...
Uses the disposable domains blacklist from [andreis/disposable-email-domains](https://github.com/andreis/disposable-email-domains) by default.

### Installation

1. Run the Composer require command to install the package:

    ```bash
    composer require propaganistas/laravel-disposable-email
    ```

2. The Service Provider will be auto-discovered. If you're running Laravel 5.4 or below, add the Service Provider manually to the end of the `$providers` array:

     ```php
    'providers' => [
        ...
     
        Propaganistas\LaravelDisposableEmail\DisposableEmailServiceProvider::class,
    ],
    ```

3. Publish the configuration file and adapt the configuration as desired:

	```bash
    php artisan vendor:publish --tag=laravel-disposable-email
    ```

4. Run the following artisan command to fetch an up-to-date list of disposable domains:
    
    ```bash
    php artisan disposable:update
    ```

5. (optional) In your languages directory, add for each language an extra language line for the validator:

	```php
	'indisposable' => 'Disposable email addresses are not allowed.',
	```

6. (optional) It's highly advised to update the disposable domains list regularly. You can either run the command yourself now and then or, if you make use of Laravel's scheduler, include it over there (`App\Console\Kernel`):
    
    ```php
    protected function schedule(Schedule $schedule)
	{
        $schedule->command('disposable:update')->weekly();
	}
    ```

### Usage

Use the `indisposable` validator to ensure a given field doesn't hold a disposable email address. You'll probably want to add it after the `email` validator to make sure a valid email is passed through:

```php
'field' => 'email|indisposable',
```
