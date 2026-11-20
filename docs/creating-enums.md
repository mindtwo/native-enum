# Creating enums

[← All documentation](index.md)

## Artisan command

```bash
# Plain enum
php artisan make:enum UserRole

# Localized enum (implements LocalizedEnum and reads labels from lang files)
php artisan make:enum UserRole --localized
```

Both create `app/Enums/UserRole.php` with three placeholder cases:

```php
namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum UserRole: int
{
    use BaseEnum;

    case OptionOne = 1;
    case OptionTwo = 2;
    case OptionThree = 3;
}
```

### Namespaces

The name you pass decides where the enum ends up:

| Command | Class |
| --- | --- |
| `make:enum UserRole` | `App\Enums\UserRole` |
| `make:enum Orders/OrderStatus` | `App\Enums\Orders\OrderStatus` |
| `make:enum App/Domain/Orders/OrderStatus` | `App\Domain\Orders\OrderStatus` |

A name is placed below `App\Enums` unless it starts with your application's root namespace.

### Custom stubs

Publish the stubs to change what `make:enum` generates:

```bash
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=stubs
```

This writes `stubs/enum.stub` and `stubs/enum-localized.stub` to the base of your project. The command uses these files when they exist and falls back to the bundled stubs otherwise.

## Writing an enum by hand

An enum needs two things: a backing type and the `BaseEnum` trait.

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

The backing type can be `int` or `string`. Pure enums without a backing type are not supported, because the helpers work with each case's `->value`.

## Optional interfaces

Add these when you need the feature behind them:

| Interface | Enables | Details |
| --- | --- | --- |
| `Illuminate\Contracts\Database\Eloquent\Castable` | The package's Eloquent casts | [Eloquent casts](casts.md) |
| `mindtwo\NativeEnum\Contracts\LocalizedEnum` | Translated labels | [Localization](localization.md) |

```php
use Illuminate\Contracts\Database\Eloquent\Castable;
use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

enum UserRole: int implements Castable, LocalizedEnum
{
    use BaseEnum;

    case ADMIN = 10;
    case CUSTOMER = 50;
}
```

The trait already contains the methods both interfaces require.

## Related

- [Helper methods](helpers.md): what the trait adds to your enum
- [Get started](get-started.md): installation and publishable assets
