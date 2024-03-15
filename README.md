# Laravel Disposable Email

![Tests](https://github.com/Propaganistas/Laravel-Disposable-Email/workflows/Tests/badge.svg?branch=master)
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

3. Run the following artisan command to fetch an up-to-date list of disposable domains:
    
    ```bash
    php artisan disposable:update
    ```

4. (optional) In your languages directory, add for each language an extra language line for the validator:

    ```php
    'indisposable' => 'Disposable email addresses are not allowed.',
    ```

5. (optional) It's highly advised to update the disposable domains list regularly. You can either run the command yourself now and then or, if you make use of Laravel's scheduler, you can register the `disposable:update` command: 

   In `routes/console.php`:
    ```php
    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('disposable:update')->weekly();
    ```

    Or if you use Laravel 10 or below, head over to the Console kernel:
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

### Custom fetches

By default the package retrieves a new list by using `file_get_contents()`. 
If your application has different needs (e.g. when behind a proxy) please review the `disposable-email.fetcher` configuration value.
