# Validation

[← All documentation](index.md)

The package ships three rules. Each is available as a rule object and as a string rule:

| Rule object | String syntax | Passes when the input is |
| --- | --- | --- |
| `new EnumValue(UserRole::class)` | `'enum_value:'.UserRole::class` | a case value, such as `10` |
| `new EnumName(UserRole::class)` | `'enum_name:'.UserRole::class` | a case name, such as `'ADMIN'` |
| `new Enum(UserRole::class)` | `'enum:'.UserRole::class` | a case instance, such as `UserRole::ADMIN` |

All three live in the `mindtwo\NativeEnum\Rules` namespace. A rule object throws an `InvalidArgumentException` if the class you pass does not exist.

## `EnumValue`

```php
use mindtwo\NativeEnum\Rules\EnumValue;

$request->validate([
    'role' => ['required', new EnumValue(UserRole::class)],
]);
```

### Strict and non-strict

The rule is strict by default: for an int-backed enum the int `10` passes and the string `'10'` fails. That suits JSON request bodies, which keep their types.

Form fields and query strings always arrive as strings. Pass `false` as the second argument to compare string forms instead:

```php
new EnumValue(UserRole::class, false);  // accepts 10 and '10'
```

With the string syntax, the second parameter is `true` or `false`:

```php
'role' => ['required', 'enum_value:'.UserRole::class],          // strict
'role' => ['required', 'enum_value:'.UserRole::class.',false'], // non-strict
```

### Input of another type

Non-strict mode compares ints and strings only. Any other input fails validation. That covers an array such as `role[]=10`, and it covers `true` and `10.0` in a JSON body, whose string forms would be `'1'` and `'10'`.

## `EnumName`

Useful when the case name travels in a URL, for example as a sort or filter parameter:

```php
use mindtwo\NativeEnum\Rules\EnumName;

$request->validate([
    'sort' => ['required', new EnumName(UserRole::class)],
]);
```

The name has to match exactly, including case. Input that is not a string, such as an array, fails validation.

## `Enum`

Checks that the value is an instance of the enum:

```php
use mindtwo\NativeEnum\Rules\Enum;

Validator::make($data, [
    'role' => ['required', new Enum(UserRole::class)],
]);
```

Request input never contains enum instances, so this rule is for data you have assembled in PHP. For raw request data use `EnumValue` or `EnumName`.

## Error messages

The rules look for a message in your application first and fall back to the package:

1. `validation.enum_value`, `validation.enum_name` or `validation.enum` in your app's `lang/{locale}/validation.php`
2. The bundled translations, available in English and German

To edit the bundled messages, publish them:

```bash
php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider" --tag=translations
```

They are written to `lang/vendor/native-enum/{locale}/messages.php` with the keys `enum`, `enum_value` and `enum_name`.

## After validation

A validated value is still a raw int or string. Turn it into a case before using it:

```php
$role = UserRole::from((int) $request->input('role'));
```

This matters when you assign to a [cast attribute](casts.md), which only accepts a case or a value of the exact backing type.

## Related

- [Helper methods](helpers.md): `hasValue()` and `hasName()`, which the rules use
- [Eloquent casts](casts.md): store the validated value on a model
