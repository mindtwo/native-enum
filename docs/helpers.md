# Helper methods

[← All documentation](index.md)

Every enum that uses the `BaseEnum` trait gets the methods on this page. The examples use this enum:

```php
enum UserRole: int
{
    use BaseEnum;

    case ADMIN = 10;
    case CUSTOMER = 50;
}
```

String-backed enums work the same way; their values are strings instead of ints.

PHP converts canonical numeric-string array keys such as `'0'` and `'10'` to integers. The keys
returned by `asArray()`, `asCollection()`, `asSelectArray()` and `asServableEnum()` therefore follow
PHP's array-key rules. The nested `value` field in `asServableEnum()` still preserves the enum's
actual backing type.

## Lists

| Call | Returns |
| --- | --- |
| `UserRole::getValues()` | `[10, 50]` |
| `UserRole::getNames()` | `['ADMIN', 'CUSTOMER']` |
| `UserRole::asArray()` | `[10 => 'ADMIN', 50 => 'CUSTOMER']` |
| `UserRole::asCollection()` | The same as `asArray()`, as an `Illuminate\Support\Collection` |
| `UserRole::asSelectArray()` | `[10 => 'ADMIN', 50 => 'CUSTOMER']` |
| `UserRole::asServableEnum()` | One entry per case, see below |

`asSelectArray()` maps each value to its label and sorts by label. The label is the translated name if the enum is [localized](localization.md), and the case name otherwise.

`asServableEnum()` is keyed by value and sorted by case name:

```php
[
    10 => ['name' => 'ADMIN', 'value' => 10, 'localized_name' => 'ADMIN'],
    50 => ['name' => 'CUSTOMER', 'value' => 50, 'localized_name' => 'CUSTOMER'],
]
```

It is the structure the [JavaScript component](javascript.md) outputs.

Numeric-string backing values can make the outer keys integers and can make JSON encode the result
as an array. Always read entries through `Object.values()` in JavaScript; each entry's nested
`value` remains the exact int or string declared by the enum.

## Lookups

| Call | Returns |
| --- | --- |
| `UserRole::getInstance(10)` | `UserRole::ADMIN` |
| `UserRole::getName(10)` | `'ADMIN'` |
| `UserRole::getValue('ADMIN')` | `10` |
| `UserRole::getValue(UserRole::ADMIN)` | `10` |
| `UserRole::getValues('ADMIN')` | `[10]` |
| `UserRole::getValues([UserRole::ADMIN, UserRole::CUSTOMER])` | `[10, 50]` |
| `UserRole::getNames(10)` | `['ADMIN']` |
| `UserRole::getNames([10, 50])` | `['ADMIN', 'CUSTOMER']` |

`getValues()` accepts case names, case instances, or an array of either. `getNames()` accepts a value or an array of values.

Two rules apply to all lookups:

- **They compare strictly.** `UserRole::getInstance('10')` does not match the int value `10`. Convert request input first, for example with `(int)` or PHP's own `UserRole::from()`.
- **They throw when nothing matches.** The exception is `Illuminate\Support\ItemNotFoundException`. Use the checks below if the input has not been validated.

## Checks

| Call | Returns |
| --- | --- |
| `UserRole::hasValue(10)` | `true` |
| `UserRole::hasValue('10')` | `false` |
| `UserRole::hasValue('10', false)` | `true` |
| `UserRole::hasName('ADMIN')` | `true` |
| `UserRole::isLocalizable()` | `false`, or `true` if the enum implements `LocalizedEnum` |

`hasValue()` is strict by default. With `false` as the second argument it compares the string forms, so `'10'` matches `10`.

## Value shortcuts

Calling a case name as a static method returns its value, and so does invoking a case:

```php
UserRole::ADMIN();        // 10

$role = UserRole::CUSTOMER;
$role();                  // 50
```

A name that is not a case, such as `UserRole::UNKNOWN()`, throws `mindtwo\NativeEnum\Exceptions\UndefinedCaseError`. Its message matches PHP's own "Undefined constant" wording.

## Random cases

| Call | Returns |
| --- | --- |
| `UserRole::getRandomValue()` | `10` or `50` |
| `UserRole::getRandomName()` | `'ADMIN'` or `'CUSTOMER'` |
| `UserRole::getRandomInstance()` | `UserRole::ADMIN` or `UserRole::CUSTOMER` |

These use `array_rand()` and are meant for factories and seeders. They are not cryptographically secure, so don't use them for tokens or IDs.

## Labels

| Call | Returns |
| --- | --- |
| `UserRole::ADMIN->name` | `'ADMIN'`, PHP's case name |
| `UserRole::ADMIN->name()` | The translated label, or `'ADMIN'` without a translation |
| `UserRole::getLocalizedName('ADMIN')` | The same, looked up by case name |

See [Localization](localization.md) for how labels are resolved.

## Related

- [Validation](validation.md): check input before a lookup
- [Eloquent casts](casts.md): let models do the conversion
