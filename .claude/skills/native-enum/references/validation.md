# Validation

Three rule classes, one per validation concern. All implement `Illuminate\Contracts\Validation\Rule`.

| Rule object                      | String form                     | Passes when the input is                   |
| -------------------------------- | ------------------------------- | ------------------------------------------ |
| `new EnumValue(UserRole::class)` | `'enum_value:'.UserRole::class` | a case value (`10`)                        |
| `new EnumName(UserRole::class)`  | `'enum_name:'.UserRole::class`  | a case name (`'ADMIN'`)                    |
| `new Enum(UserRole::class)`      | `'enum:'.UserRole::class`       | a case instance (`UserRole::ADMIN`)        |

## `EnumValue` — validate a primitive value

```php
use mindtwo\NativeEnum\Rules\EnumValue;

public function store(Request $request)
{
    $this->validate($request, [
        'role' => ['required', new EnumValue(UserRole::class)],
    ]);
}
```

`passes()` calls `UserRole::hasValue($value, $strict)`. The default strict flag is `true`.

### Strict vs non-strict

Strict mode suits JSON request bodies, which keep their types. Form fields and query strings always arrive as strings, so `'10'` fails strict validation against an int-backed enum. Pass `false` as the second argument to compare string forms instead:

```php
new EnumValue(UserRole::class, false);
```

```php
UserRole::hasValue('10', false); // true   ← string forms compared
UserRole::hasValue('10');        // false  ← strict
```

### String-rule form

```php
'role' => ['required', 'enum_value:'.UserRole::class],           // strict
'role' => ['required', 'enum_value:'.UserRole::class.',false'],  // non-strict
```

The string form is registered by `EnumServiceProvider::bootValidators()`. Only use `true` or `false` as the second parameter.

## `EnumName` — validate a case name

```php
use mindtwo\NativeEnum\Rules\EnumName;

public function index(Request $request)
{
    $this->validate($request, [
        'sort' => ['bail', 'required', 'string', new EnumName(UserRole::class)],
    ]);
}
```

`passes()` calls `UserRole::hasName($value)`. The match is exact and case-sensitive. Use this for URL parameters like `/users?sort=ADMIN`.

## `Enum` — validate a case instance

```php
use mindtwo\NativeEnum\Rules\Enum;

Validator::make($data, [
    'role' => ['required', new Enum(UserRole::class)],
]);
```

`passes()` returns `$value instanceof $enumClass`. Raw request input never contains enum instances, so this rule **always fails on request data**. Use it for data assembled in PHP (DTOs, arrays passed to `Validator::make()`) where an upstream layer is supposed to have produced a case.

## Constructor validation

All three rules check `class_exists()` in their constructor and throw `InvalidArgumentException` if the FQCN is bogus:

```php
new EnumValue('App\\Enums\\DoesNotExist');
// InvalidArgumentException: Cannot validate against the enum, the class App\Enums\DoesNotExist doesn't exist.
```

The exception is thrown when the rule object is constructed, i.e. when the rules array is built for a request — not at application boot.

## Error messages

Each rule's `message()` resolves in this order:

1. `trans('validation.enum_value')` / `enum_name` / `enum` if your app defines it
2. Otherwise, `__('native-enum::messages.enum_value')` (bundled with the package, available in `en` and `de`)

Publish the package translations to override them per-app:

```bash
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=translations
# edits land in lang/vendor/native-enum/{en,de}/messages.php
```

If you maintain your own `validation.php`, prefer setting `enum`, `enum_name`, and `enum_value` there over publishing the package translations — it keeps validation strings in one place.

## Combining with other rules

Laravel runs **all** rules of a field unless `bail` is present, so a type rule in front of the enum rule does not short-circuit on its own. Add `bail` plus a type rule to keep non-scalar input away from the enum rules:

```php
'role' => ['bail', 'required', 'integer', new EnumValue(UserRole::class, false)],
'sort' => ['bail', 'required', 'string', new EnumName(UserRole::class)],
```

Without this, array input such as `role[]=10` raises an error inside a non-strict `EnumValue` (array-to-string conversion) or inside `EnumName` (`hasName()` is typed `string`) instead of producing a validation failure.

Laravel's `integer` rule accepts numeric strings like `'10'`, so it works together with non-strict `EnumValue` for form input.

## After validation

A validated value is still a raw int or string — validation does not convert it. Lookups and casts compare strictly, so convert before use:

```php
$role = UserRole::from((int) $request->input('role'));

$user->role = $role; // cast attribute: accepts a case or a value of the exact backing type
```

Assigning the still-string `'10'` to a cast attribute throws `ItemNotFoundException` even though non-strict validation passed. See [casts.md](casts.md).
