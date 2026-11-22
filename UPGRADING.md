# Upgrading

- [From 2.x to 3.x](#from-2x-to-3x)

## From 2.x to 3.x

### Validation messages

The built-in default message has been removed. Add an `indisposable` entry to each language's `validation.php` file, including any custom message previously assigned to that property:

```php
'indisposable' => 'Disposable email addresses are not allowed.',
```

Without a translation, the validation message may display the `validation.indisposable` key instead.

### Validation rule

The rule now checks that the value is a valid email address before checking its domain, so malformed addresses that previously passed the domain check will fail. Continue to use Laravel's `email` rule alongside it:

```php
'email' => ['email', 'indisposable'],
```