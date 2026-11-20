# Create an enum

There are two paths: artisan generator or hand-written. The artisan generator is just a thin wrapper around stubs, so anything it produces you can write by hand.

## Artisan

```bash
# Plain enum
php artisan make:enum UserRole

# Implements mindtwo\NativeEnum\Contracts\LocalizedEnum
php artisan make:enum UserRole --localized

# Nested below App\Enums
php artisan make:enum Orders/OrderStatus
# → App\Enums\Orders\OrderStatus

# Outside App\Enums: start with the root namespace
php artisan make:enum App/Domain/Orders/OrderStatus
# → App\Domain\Orders\OrderStatus
```

Output (plain):

```php
<?php

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

Output (`--localized`):

```php
<?php

namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;

    case OptionOne = 1;
    case OptionTwo = 2;
    case OptionThree = 3;
}
```

The default namespace is `App\Enums`. A name is nested below it unless the name starts with the application's root namespace (`App/…`), in which case it is used as given.

### Custom stubs

Publish once:

```bash
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=stubs
```

Edits land in `stubs/enum.stub` and `stubs/enum-localized.stub`. The command resolves stubs from there first, then falls back to the package's bundled copies.

Common customizations:
- Add a license header (`// SPDX-License-Identifier: MIT`)
- Pin the backing type (e.g. always `: int`)
- Inject a project-specific interface alongside `LocalizedEnum`

## Hand-written

If you can't or don't want to use artisan, the structure is:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;

enum UserRole: int
{
    use BaseEnum;

    case ADMIN    = 10;
    case CUSTOMER = 50;
}
```

### Backing types

PHP supports `int`, `string`, or no backing type. `BaseEnum` needs one of the first two:

| Backing | `->value` type | Notes                                         |
| ------- | -------------- | --------------------------------------------- |
| `int`   | `int`          | Fast comparisons, default in the stubs        |
| `string`| `string`       | Self-describing in DB, but watch storage size |
| none    | —              | Not supported: `->value` doesn't exist        |

`BaseEnum` requires a **backed enum**. On a pure enum only the name-based helpers (`getNames()`, `hasName()`) work; everything that reads `->value` — `asArray()`, `getValues()`, `asSelectArray()`, the casts, `__callStatic()` — fails with `Undefined property`.

### Optional interfaces

| Interface                                         | Needed for                                  |
| ------------------------------------------------- | ------------------------------------------- |
| `Illuminate\Contracts\Database\Eloquent\Castable` | The package's Eloquent casts ([casts.md](casts.md)) — required there |
| `mindtwo\NativeEnum\Contracts\LocalizedEnum`      | Translated labels ([localized.md](localized.md)) |

The trait already contains the methods both interfaces require; the enum only declares them.

### Mixing in project interfaces

If your domain has a marker interface, declare it alongside:

```php
namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;
use App\Contracts\HasMetadata;  // your interface

enum OrderStatus: int implements LocalizedEnum, HasMetadata
{
    use BaseEnum;

    case DRAFT     = 10;
    case PUBLISHED = 20;
}
```

The trait stays compatible as long as the other interfaces don't conflict on method names. Besides the static helpers, `BaseEnum` defines three instance methods: `name()`, `toLivewire()` and `__invoke()`.
