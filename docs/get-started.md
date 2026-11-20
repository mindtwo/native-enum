# Get started

[← All documentation](index.md)

## Requirements

- PHP 8.2 or higher
- Laravel 8 or higher

## Installation

```bash
composer require mindtwo/native-enum
```

The service provider is auto-discovered, so no manual registration is needed.

## Publish optional assets

Nothing has to be published to use the package. Publish an asset only when you want to change it:

| Asset | Tag | Published to | Needed for |
| --- | --- | --- | --- |
| Generator stubs | `stubs` | `stubs/enum.stub`, `stubs/enum-localized.stub` | [Customizing `make:enum`](creating-enums.md#custom-stubs) |
| Validation messages | `translations` | `lang/vendor/native-enum/{locale}/messages.php` | [Changing error messages](validation.md#error-messages) |
| Config file | none | `config/enums.php` | [Serving enums to JavaScript](javascript.md) |

```bash
# One asset
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=stubs
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=translations

# Everything, including config/enums.php
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider"
```

The config file has no tag of its own, so it is published together with the other assets by the last command.

## Your first enum

Generate it:

```bash
php artisan make:enum UserRole
```

Or write it by hand:

```php
namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum UserRole: int
{
    use BaseEnum;

    case ADMIN = 10;
    case CUSTOMER = 50;
}
```

Then use it:

```php
UserRole::asSelectArray();   // [10 => 'ADMIN', 50 => 'CUSTOMER'], ready for a dropdown
UserRole::getInstance(10);   // UserRole::ADMIN
UserRole::ADMIN();           // 10
UserRole::hasName('ADMIN');  // true

new \mindtwo\NativeEnum\Rules\EnumValue(UserRole::class);  // validation rule
```

## Next steps

- [Creating enums](creating-enums.md): namespaces, localized enums, custom stubs
- [Helper methods](helpers.md): the full method reference
- [Validation](validation.md): validate request input against an enum
- [Eloquent casts](casts.md): store enums on models
- [Localization](localization.md): translated labels
- [Livewire](livewire.md) and [Serving enums to JavaScript](javascript.md): enums in the frontend
