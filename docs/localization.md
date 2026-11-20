# Localization

[← All documentation](index.md)

A localized enum has a translated label for each case, for example for a `<select>` or a table column.

## Set up

1. Implement `LocalizedEnum`, or generate the enum with `php artisan make:enum UserRole --localized`:

   ```php
   namespace App\Enums;

   use mindtwo\NativeEnum\BaseEnum;
   use mindtwo\NativeEnum\Contracts\LocalizedEnum;

   enum UserRole: int implements LocalizedEnum
   {
       use BaseEnum;

       case ADMIN = 10;
       case CUSTOMER = 50;
   }
   ```

2. Add the labels to `lang/{locale}/enums.php`, keyed by the enum class and the case name:

   ```php
   // lang/en/enums.php
   return [
       \App\Enums\UserRole::class => [
           'ADMIN' => 'Administrator',
           'CUSTOMER' => 'Customer',
       ],
   ];
   ```

## Reading labels

| Call | Returns |
| --- | --- |
| `UserRole::ADMIN->name()` | `'Administrator'` |
| `UserRole::ADMIN->name` | `'ADMIN'`, PHP's case name, never translated |
| `UserRole::getLocalizedName('ADMIN')` | `'Administrator'` |
| `UserRole::asSelectArray()` | `[10 => 'Administrator', 50 => 'Customer']`, sorted by label |
| `UserRole::asServableEnum()` | Each entry's `localized_name` holds the label |

Labels follow the current application locale.

## Fallbacks

The case name is returned instead of a label when:

- the enum does not implement `LocalizedEnum`,
- there is no translation for the case in the current locale or in the application's fallback locale, or
- no translator is available, for example in a unit test without a Laravel application.

## Custom translation key

By default the key is `enums.<fully qualified class name>.<case name>`. Define `getLocalizationName()` on the enum to use a different prefix:

```php
enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;

    case ADMIN = 10;
    case CUSTOMER = 50;

    public static function getLocalizationName(): string
    {
        return 'enums.user_role';
    }
}
```

```php
// lang/en/enums.php
return [
    'user_role' => [
        'ADMIN' => 'Administrator',
        'CUSTOMER' => 'Customer',
    ],
];
```

## Related

- [Helper methods](helpers.md#labels): all label-related methods
- [Serving enums to JavaScript](javascript.md): labels in the frontend
