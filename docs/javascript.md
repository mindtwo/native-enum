# Serving enums to JavaScript

[← All documentation](index.md)

The `<x-m2-enums::serve />` Blade component writes selected enums into the page as `window.m2_ServedEnums`. A Vue, React or Alpine frontend can then read values and labels without an extra request.

## Set up

1. Publish the config file. This step is required: the component reads `config('enums.serve')`, and the package does not register a default for it.

   ```bash
   php artisan vendor:publish --provider="mindtwo\NativeEnum\EnumServiceProvider"
   ```

2. List the enums to expose in `config/enums.php`:

   ```php
   return [
       'serve' => [
           \App\Enums\UserRole::class,
           \App\Enums\OrderStatus::class,
       ],
   ];
   ```

3. Render the component once in your layout, before the scripts that read it:

   ```blade
   <x-m2-enums::serve />
   ```

## Output

```js
window.m2_ServedEnums = {
    UserRole: {
        10: { name: 'ADMIN', value: 10, localized_name: 'Administrator' },
        50: { name: 'CUSTOMER', value: 50, localized_name: 'Customer' },
    },
    OrderStatus: { /* ... */ },
};
```

- Each enum is stored under its short class name. Two enums with the same short name in different namespaces overwrite each other.
- The cases are keyed by value. An enum whose values are consecutive ints starting at `0` is output as a plain array instead.
- `localized_name` is the translated label of a [localized enum](localization.md), and the case name otherwise.

`Object.values()` works for both shapes:

```js
const roles = Object.values(window.m2_ServedEnums.UserRole);

roles.map((role) => ({ value: role.value, label: role.localized_name }));
```

## Things to keep in mind

- **Everything listed is public.** Case names, values and labels are visible to anyone who can load the page. Don't list enums whose cases are internal.
- **Form controls return strings.** A `<select>` gives you `'10'`, not `10`. Convert with `Number()` before sending it, or validate with a [non-strict `EnumValue` rule](validation.md#strict-and-non-strict).

## Your own endpoint

The component outputs `asServableEnum()` for each configured enum. Call it yourself if you prefer to load enums on demand:

```php
Route::get('/enums/user-role', fn () => UserRole::asServableEnum());
```

## Related

- [Helper methods](helpers.md#lists): `asServableEnum()` and `asSelectArray()`
- [Livewire](livewire.md): enums on Livewire components
