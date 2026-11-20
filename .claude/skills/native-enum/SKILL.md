---
name: native-enum
description: Use the mindtwo/native-enum Laravel package to bridge PHP native enums with Laravel — generate enums with `make:enum`, validate request data via `EnumValue`/`EnumName`/`Enum` rules, cast Eloquent attributes to enums (single, nullable, or `:collection`, optionally with `comma` storage), localize case names through `lang/enums.php`, expose enums to JavaScript through `<x-m2-enums::serve />`, and use enums as Livewire component properties. Activate when the user mentions "native-enum", `mindtwo/native-enum`, `BaseEnum`, `EnumValue`/`EnumName`/`Enum` rules, `EnumCast`/`EnumCollectionCast`, `LocalizedEnum`, or any task that involves PHP native enums in a Laravel project.
license: MIT
metadata:
  package: mindtwo/native-enum
  php: ">=8.2"
  laravel: "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0"
---

# mindtwo/native-enum — Laravel-native PHP enums

A `BaseEnum` trait that drops onto any native PHP enum and gives it Laravel-flavoured superpowers: helper methods, validation rules, Eloquent casts, localization and JS serving. There are no base classes to extend. Two interfaces are opt-in: `Castable` (**required** for the Eloquent casts) and `LocalizedEnum` (for translated labels).

## When to reach for this skill

Activate this skill when the user is:

- Creating a PHP native enum in a Laravel app (`enum Status: int { ... }`)
- Generating enums via `php artisan make:enum`
- Asking how to validate that a request field is a valid enum value/name/instance
- Wiring Eloquent `$casts` for an enum-typed column, including nullable or collection variants
- Wanting user-facing labels for enum cases (dropdowns, table filters, badges)
- Pushing enum options to a JavaScript frontend without round-tripping to the backend
- Binding an enum property on a Livewire component

Skip this skill for: pure PHP enum usage with no Laravel, OR for non-Laravel enum libraries (`BenSampo/laravel-enum`, `archtechx/enums`, `spatie/laravel-enum`, …).

## Quick start (60-second tour)

```php
// 1. Generate the enum
// php artisan make:enum UserRole --localized

// 2. Edit it (the trait unlocks everything below)
namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;

    case ADMIN    = 10;
    case CUSTOMER = 50;
}

// 3. Use anywhere
UserRole::asSelectArray();             // ['10' => 'Administrator', '50' => 'Customer']
UserRole::ADMIN();                     // 10   (via __callStatic)
UserRole::ADMIN->name();               // 'Administrator'  (translated)
UserRole::hasValue(10);                // true
UserRole::hasValue('10', strict: false); // true
UserRole::getInstance(10);             // UserRole::ADMIN
UserRole::getValue('ADMIN');           // 10
```

## Task index

For deeper coverage of each capability, see the linked reference. Don't load them all at once — pick the one(s) the current task needs.

| Task                                      | Reference                              |
| ----------------------------------------- | -------------------------------------- |
| Generate or hand-write an enum            | [create-enum.md](references/create-enum.md) |
| All `BaseEnum` helper methods             | [base-usage.md](references/base-usage.md) |
| Validate request input                    | [validation.md](references/validation.md) |
| Cast Eloquent attributes (single/null/collection) | [casts.md](references/casts.md) |
| Localize case labels                      | [localized.md](references/localized.md) |
| Expose enums to JavaScript                | [js-serving.md](references/js-serving.md) |
| Use enums in Livewire components          | [livewire.md](references/livewire.md) |

## Hard rules — things that look fine but are wrong

These come from real bugs in this package and adjacent PHP gotchas. Violating them produces silent data corruption or runtime errors.

1. **Never `implode` an array of enum cases.** PHP enums (int- AND string-backed) do not implement `__toString()`. `implode(',', [Status::Open])` throws `Object of class Status could not be converted to string`. Always project to `->value` first: `implode(',', array_map(fn($c) => $c->value, $cases))`. The package's own `EnumCollectionCast` had this bug in the `:collection,comma` format; it's fixed in tests `testCommaFormatRoundtripForIntBackedEnum` / `testCommaFormatCoercesStringValuesToIntForIntBackedEnum`.

2. **Never rely on `UserRole::ADMIN()` to return the enum case** — it returns the *value* (e.g. `int(10)`) because `BaseEnum` defines `__callStatic`. If you actually want the case instance, use `UserRole::ADMIN` (no parens). Calling a non-existent case (`UserRole::DOES_NOT_EXIST()`) throws `mindtwo\NativeEnum\Exceptions\UndefinedCaseError`, not the enum case itself.

3. **`hasValue()` is strict by default.** `UserRole::hasValue('10')` is `false` because PHP's `===` rejects `string '10' === int 10`. Pass `false` as the second argument to compare string forms instead. This matters most for query-string input where everything is a string. Non-strict mode compares ints and strings only: `true`, `10.0`, `null` and arrays never match.

4. **`getValue()` accepts both a case name and a case instance.** `getValue('ADMIN')` and `getValue(UserRole::ADMIN)` both return `10`. A name that matches no case throws `Illuminate\Support\ItemNotFoundException` (from `Collection::sole()`), so guard user input with `hasName()` or the `EnumName` rule first.

5. **`LocalizedEnum::name()` returns the case name unchanged if no translation exists.** Don't assume `name()` always returns a translated string — it falls back to the raw case name. This is intentional: missing translations should not crash dropdowns.

6. **`getLocalizedName()` requires a bound translator.** Outside a Laravel application context (CLI scripts, unit tests without an app, …) it returns the raw value rather than throwing. If you write code that depends on a translation being present, check `Lang::has($key)` yourself before calling.

7. **Comma storage format doesn't index.** `:collection,comma` is fine for "set of roles" where you never query by membership. For columns you `WHERE roles LIKE '%10%'` against, use JSON storage and PostgreSQL `jsonb` / MySQL JSON functions.

   Comma storage is unescaped. String-backed values must not be empty, contain a comma, or have surrounding whitespace; use the default JSON format for those values.

8. **Modifier order on casts doesn't matter, but list `:collection` first.** `'roles' => Status::class.':collection,comma,nullable'` and `'roles' => Status::class.':nullable,comma,collection'` behave identically.

9. **Lookups and casts compare with `===` — there is no type coercion.** `UserRole::getInstance('10')` throws `ItemNotFoundException` for an int-backed enum, and so does `$model->role = '10'` on a cast attribute. Request input is always a string, so convert it first: `UserRole::from((int) $request->input('role'))`. A passing non-strict `EnumValue` rule does not change the value's type. A case of a different enum is rejected as well, even if its value matches.

10. **The Eloquent casts only run if the enum `implements Castable`.** Without the interface, `'role' => UserRole::class` is handled by Laravel's built-in enum cast, and `:nullable` / `:collection` fail with `Cannot instantiate enum`. The trait supplies `castUsing()`; the enum only has to declare the interface.

11. **Collection casts take plain arrays, not `Collection` objects.** `$model->roles = collect([10, 50])` throws `ItemNotFoundException`; call `->all()` first. Reading the attribute also returns a plain array of cases.

12. **`window.m2_ServedEnums.<Enum>` can be an object or an array.** PHP array-key coercion and JSON decide the shape. Always wrap it before iterating: `Object.values(window.m2_ServedEnums.UserRole)`.

13. **Enums don't need `Livewire\Wireable`.** Livewire 3 hydrates backed enums natively; the trait's `toLivewire()` / `fromLivewire()` are plain helpers and play no part in that. Don't add the interface just to make a typed enum property work.

14. **Cases are not callable.** `$case()` no longer exists; read the value with `$case->value`. It was removed because Laravel's collection methods treat a callable argument as a callback, so `collect($roles)->contains(UserRole::ADMIN)` returned a wrong result. The static shortcut `UserRole::ADMIN()` still returns the value.

15. **`comma` needs `collection`.** `UserRole::class.':comma'` is a single-value cast; the modifier is ignored without `collection`. Always write `:collection,comma`.

## Generate an enum

```bash
# Plain
php artisan make:enum UserRole

# Localized (implements LocalizedEnum)
php artisan make:enum UserRole --localized

# Nested below App\Enums → App\Enums\Orders\OrderStatus
php artisan make:enum Orders/OrderStatus --localized
```

This creates `App\Enums\{name}`. A slash-separated name is nested **below** `App\Enums`. To place the enum somewhere else, start the name with the application's root namespace:

```bash
php artisan make:enum App/Domain/Billing/InvoiceStatus
# → App\Domain\Billing\InvoiceStatus
```

To customize the stubs (e.g. add a license header or project-specific imports), publish them once:

```bash
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=stubs
```

The command resolves stubs from `stubs/enum.stub` first, then falls back to the package's bundled copy.

## All `BaseEnum` helper methods at a glance

| Method                                  | Returns                       | Notes                                    |
| --------------------------------------- | ----------------------------- | ---------------------------------------- |
| `getValues($names = null)`              | `int[]\|string[]`             | All values, or values for given names/cases |
| `getNames($values = null)`              | `string[]`                    | All names, or names for given values     |
| `getInstance($value)`                   | `self`                        | Strict match on `->value`; throws `ItemNotFoundException` |
| `getName($value)`                       | `string`                      | Strict match; throws `ItemNotFoundException` |
| `getValue(self\|string $name)`          | `int\|string`                 | Accepts case or name; throws `ItemNotFoundException` |
| `hasValue($value, bool $strict = true)` | `bool`                        | Default strict                           |
| `hasName(string $name)`                 | `bool`                        |                                          |
| `getRandomValue()`                      | `int\|string`                 | Not cryptographically secure             |
| `getRandomName()`                       | `string`                      |                                          |
| `getRandomInstance()`                   | `self`                        |                                          |
| `asArray()`                             | `[value => name]`             |                                          |
| `asCollection()`                        | `Illuminate\Support\Collection` | Same shape as `asArray()`                |
| `asSelectArray()`                       | `[value => localized_name]`   | Sorted by label                          |
| `asServableEnum()`                      | `[value => [name, value, localized_name]]` | Keyed by value, sorted by case name; for JS consumers |
| `isLocalizable()`                       | `bool`                        | True iff implements `LocalizedEnum`      |
| `getLocalizationName()`                 | `string`                      | Default `'enums.'.static::class`         |
| `getLocalizedName($name)`               | `string\|null`                | Falls back to `$name` if not translated  |
| `case->name()`                          | `string\|null`                | Same as `getLocalizedName($case->name)`  |
| `case->toLivewire()`                    | `['value' => …, 'label' => …]`| Helper only; Livewire does not need it   |
| `static::fromLivewire($value)`          | `self`                        | Accepts a case, the `toLivewire()` array, or an object with `value`; throws `InvalidArgumentException` otherwise |
| `static::CASENAME()` (`__callStatic`)   | `int\|string`                 | Throws `UndefinedCaseError` for unknown  |
| `castUsing(array $args)`                | `CastsAttributes`             | Called by Laravel only if the enum implements `Castable` |

For full descriptions, edge cases, and code samples, see [base-usage.md](references/base-usage.md).

## Validate request input

Three rule classes — pick the one that matches what the request carries:

```php
use mindtwo\NativeEnum\Rules\EnumValue;  // request field is the primitive value (10, "10", etc.)
use mindtwo\NativeEnum\Rules\EnumName;   // request field is the case name ("ADMIN")
use mindtwo\NativeEnum\Rules\Enum;       // request field is the enum case itself (rare; usually already converted upstream)

public function store(Request $request)
{
    $this->validate($request, [
        'role' => ['required', new EnumValue(UserRole::class)],
        // or as a string rule:
        'role' => ['required', 'enum_value:'.UserRole::class],
        // strict = false is also supported in string form:
        'role' => ['required', 'enum_value:'.UserRole::class.',false'],
    ]);
}
```

For each rule: see [validation.md](references/validation.md).

## Cast Eloquent attributes

The enum has to implement `Castable` (hard rule 10); the trait provides the required `castUsing()`:

```php
use Illuminate\Contracts\Database\Eloquent\Castable;

enum UserRole: int implements Castable
{
    use BaseEnum;
    // …
}

class User extends Model
{
    protected $casts = [
        'role'          => UserRole::class,                       // EnumCast
        'previous_role' => UserRole::class.':nullable',           // allow NULL column values
        'roles'         => UserRole::class.':collection',         // EnumCollectionCast, JSON storage
        'tags'          => UserRole::class.':collection,comma',   // EnumCollectionCast, comma storage
    ];
}
```

| Modifier         | Effect                                                   | DB column type                |
| ---------------- | -------------------------------------------------------- | ----------------------------- |
| *(none)*         | Single enum case; `NULL` throws `NotNullableEnumField`   | `INT`/`VARCHAR`, matching the backing type |
| `:nullable`      | Single enum case or null                                 | Nullable                      |
| `:collection`    | Array of enum cases (JSON)                               | `JSON` / `jsonb`              |
| `:collection,comma` | Array of enum cases (comma-joined primitive values)    | `VARCHAR`/`TEXT` (small data) |

Assign a case or a raw value of the exact backing type — `'10'` for an int-backed enum throws (hard rule 9). For migrations, read/write semantics, and common errors, see [casts.md](references/casts.md).

## Localize case labels

```php
namespace App\Enums;

use mindtwo\NativeEnum\BaseEnum;
use mindtwo\NativeEnum\Contracts\LocalizedEnum;

enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;

    case ADMIN    = 10;
    case CUSTOMER = 50;
}
```

```php
// lang/en/enums.php
return [
    \App\Enums\UserRole::class => [
        'ADMIN'    => 'Administrator',
        'CUSTOMER' => 'Customer',
    ],
];
```

```php
UserRole::ADMIN->name();              // 'Administrator'
UserRole::asSelectArray();            // ['10' => 'Administrator', '50' => 'Customer']
UserRole::getLocalizedName('ADMIN');  // 'Administrator'
UserRole::getLocalizedName('UNKNOWN'); // 'UNKNOWN'  (falls back to the raw name)
```

For file layout, locale fallback, custom translation keys, and plural labels, see [localized.md](references/localized.md).

## Expose enums to JavaScript

```php
// config/enums.php (publish with `php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider"`)
return [
    'serve' => [
        \App\Enums\UserRole::class,
        \App\Enums\OrderStatus::class,
    ],
];
```

Publishing the config is required — the component errors without `config('enums.serve')`.

```blade
{{-- in your layout, once, before the scripts that read it --}}
<x-m2-enums::serve />
```

```js
console.log(window.m2_ServedEnums);
// {
//   UserRole: {
//     10: { name: 'ADMIN',    value: 10, localized_name: 'Administrator' },
//     50: { name: 'CUSTOMER', value: 50, localized_name: 'Customer' }
//   },
//   OrderStatus: { ... }
// }

Object.values(window.m2_ServedEnums.UserRole); // iterate safely across object and array shapes
```

For framework examples and a custom endpoint approach (when `<x-m2-enums::serve />` doesn't fit), see [js-serving.md](references/js-serving.md).

## Use enums in Livewire

```php
namespace App\Livewire;

use App\Enums\UserRole;
use Livewire\Component;

class UserForm extends Component
{
    public UserRole $role = UserRole::CUSTOMER;
}
```

Livewire 3 hydrates backed enums natively, so no wiring is required — and none comes from the trait. The enum doesn't need `Livewire\Wireable` (hard rule 13). See [livewire.md](references/livewire.md) for nullable properties, `wire:model` with a `<select>`, and lists of enums.

## Troubleshooting

- **`Call to undefined method MyEnum::FOO()`** — you forgot `use BaseEnum;` in the enum. Or you wrote `MyEnum::FOO()` (parens) expecting the case itself — `__callStatic` returns the *value*, not the case. Use `MyEnum::FOO` (no parens) for the case instance.
- **`Object of class MyEnum could not be converted to string`** — you're using `implode`/`sprintf %s`/`"string $enum"` directly. Project to `->value` first.
- **`NotNullableEnumField: Field X on model Y is not nullable`** — your DB column is NULL but the cast isn't marked `:nullable`. Either add the modifier or default the column to a valid case value.
- **`Cannot validate against the enum, the class X doesn't exist`** — the rule constructor was given an FQCN that can't be autoloaded. Check namespace and `composer dump-autoload`.
- **`ItemNotFoundException` from `getInstance()`/`getName()`/`getValue()` or a cast attribute** — the value or name you passed doesn't match any case, or it has the wrong type (`'10'` vs `10`). For user input, validate first with `EnumValue`/`EnumName` and convert to the backing type.
- **`Cannot instantiate enum App\Enums\X`** — a cast uses `:nullable` or `:collection` but the enum doesn't `implement Castable`. Add the interface.
- **`UnexpectedValueException` when reading a `:collection` attribute** — the column doesn't hold a JSON array (truncated or hand-edited data, a JSON `null`, a JSON scalar). Repair the row; the cast refuses to read it as an empty list, because the next save would overwrite the data.
- **`Object of type App\Enums\X is not callable`** — code calls `$case()`. Use `$case->value` (hard rule 14).
- **`getLocalizedName()` returns the raw name in production** — your `lang/{locale}/enums.php` doesn't have the case, OR the translator isn't bound (rare in Laravel). Check `php artisan tinker: Lang::has('enums.'.UserRole::class.'.ADMIN')`.
- **`make:enum Domain/Foo` lands in `App\Enums\Domain\Foo`** — names are nested below `App\Enums` unless they start with the root namespace. Use `php artisan make:enum App/Domain/Foo` for `App\Domain\Foo`.

## Out of scope

- Non-Laravel PHP enum usage — there are no Laravel-specific hooks to wire.
- Enum schemas / bitmask enums — `BaseEnum` is a 1:1 mapping of cases to primitives. Compose cases manually if you need bitflags.
- Enum-driven frontend code generation — this package ships no Vite plugin / Blade directive generation. Pair it with [spatie/laravel-data](https://spatie.be/docs/laravel-data) or [typescript-transformer](https://spatie.be/docs/typescript-transformer) for that.
- Multilingual translations stored in the database — `LocalizedEnum` only reads from Laravel's lang files. If you need DB-driven labels, write your own override of `getLocalizedName()` in the enum.
