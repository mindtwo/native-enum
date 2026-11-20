# Eloquent casts

[← All documentation](index.md)

The package has two casts: `EnumCast` for a single case and `EnumCollectionCast` for a list of cases. You don't reference them directly. You cast to the enum class and pick the behaviour with modifiers.

## Requirement: implement `Castable`

Laravel only hands the cast to the package if the enum implements `Illuminate\Contracts\Database\Eloquent\Castable`. The trait already provides the `castUsing()` method the contract requires, so adding the interface is enough:

```php
namespace App\Enums;

use Illuminate\Contracts\Database\Eloquent\Castable;
use mindtwo\NativeEnum\BaseEnum;

enum UserRole: int implements Castable
{
    use BaseEnum;

    case ADMIN = 10;
    case CUSTOMER = 50;
}
```

Without the interface:

- `'role' => UserRole::class` is handled by Laravel's built-in enum cast, not by this package.
- The `:nullable` and `:collection` modifiers fail with an error, because Laravel tries to instantiate the enum as a cast class.

## Overview

| Cast | Column content | Attribute value | Suggested column |
| --- | --- | --- | --- |
| `UserRole::class` | `10` | `UserRole::ADMIN` | integer or string, matching the backing type |
| `UserRole::class.':nullable'` | `10` or `NULL` | `UserRole::ADMIN` or `null` | the same, nullable |
| `UserRole::class.':collection'` | `[10,50]` | array of cases | `json` |
| `UserRole::class.':collection,comma'` | `10,50` | array of cases | `string` or `text` |

Modifiers can be combined, for example `UserRole::class.':collection,comma,nullable'`. Their order does not matter.

## Single value

```php
namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $casts = [
        'role' => UserRole::class,
    ];
}
```

```php
$user->role;                      // UserRole::ADMIN

$user->role = UserRole::CUSTOMER; // stores 50
$user->role = 50;                 // stores 50
```

The cast compares strictly. A value of the wrong type, such as the string `'50'` for an int-backed enum, or a value that is not a case, throws `Illuminate\Support\ItemNotFoundException`. Convert request input before assigning it:

```php
$user->role = UserRole::from((int) $request->input('role'));
```

## `:nullable`

Without the modifier, a `NULL` column or assigning `null` throws `mindtwo\NativeEnum\Exceptions\NotNullableEnumField`. Add `:nullable` to allow it:

```php
protected $casts = [
    'role' => UserRole::class.':nullable',
];
```

## `:collection`

Stores a list of cases in one column, JSON-encoded by default:

```php
protected $casts = [
    'roles' => UserRole::class.':collection',
];
```

```php
$user->roles = [UserRole::ADMIN, UserRole::CUSTOMER]; // stores [10,50]
$user->roles = [10, 50];                              // stores [10,50]

$user->roles; // [UserRole::ADMIN, UserRole::CUSTOMER]
```

The attribute is a plain PHP array of cases, not a `Collection`. Assign a plain array as well: a single case or value is wrapped into an array for you, but a `Collection` is not accepted, so call `->all()` on it first.

## `:comma`

If the list has to live in a plain text column, add `comma` to store the values comma-separated:

```php
protected $casts = [
    'roles' => UserRole::class.':collection,comma',
];
```

```php
$user->roles = [UserRole::ADMIN, UserRole::CUSTOMER]; // stores "10,50"
```

On reading, whitespace around each value is trimmed, values of an int-backed enum are converted back to ints, and an empty string becomes an empty array. A part that is not a case, such as `abc`, throws `Illuminate\Support\ItemNotFoundException`.

Caveats:

- A string-backed enum whose values contain a comma cannot be stored this way.
- The column cannot be queried or indexed per value. Prefer JSON if you need to search it.

## Related

- [Validation](validation.md): validate input before assigning it
- [Creating enums](creating-enums.md#optional-interfaces): the interfaces an enum can implement
