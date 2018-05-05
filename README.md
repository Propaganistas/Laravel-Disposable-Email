# Laravel Disposable Email

[![Latest Stable Version](https://poser.pugx.org/propaganistas/laravel-disposable-email/v/stable)](https://packagist.org/packages/propaganistas/laravel-disposable-email)
[![Total Downloads](https://poser.pugx.org/propaganistas/laravel-disposable-email/downloads)](https://packagist.org/packages/propaganistas/laravel-disposable-email)
[![License](https://poser.pugx.org/propaganistas/laravel-disposable-email/license)](https://packagist.org/packages/propaganistas/laravel-disposable-email)

Adds a validator to Laravel for checking whether a given email address isn't originating from disposable email services such as `Mailinator`, `Guerillamail`, ...
Uses the disposable domains blacklist from [andreis/disposable-email-domains](https://github.com/andreis/disposable-email-domains).

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

3. Run the following artisan command to fetch an up-to-date list of disposable domains:
    
    ```bash
    php artisan disposable:cache
    ```

4. (optional) In your languages directory, add for each language an extra language line for the validator:

	```php
	'indisposable' => 'Disposable email addresses are not allowed.',
	```

5. (optional) It's highly advised to update the disposable domains list regularly. You can either run the command yourself now and then or, if you make use of Laravel's scheduler, include it over there (`App\Console\Kernel`):
    
    ```php
    protected function schedule(Schedule $schedule)
	{
        $schedule->command('disposable:cache')->weekly();
	}
    ```

### Usage

Use the `indisposable` validator to ensure a given field doesn't hold a disposable email address. You'll probably want to add it after the `email` validator to make sure a valid email is passed through:

```php
'emailfield' => 'email|indisposable',
```
