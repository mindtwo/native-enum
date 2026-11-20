# Localized enum

When you want dropdowns and badges to show "Administrator" instead of "ADMIN", implement `mindtwo\NativeEnum\Contracts\LocalizedEnum`. Translations live in Laravel's lang files at the conventional location.

## Declare the interface

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

## Translation file

Default location: `lang/{locale}/enums.php`. Use the fully-qualified class name as the top-level key, then case names as the inner keys:

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
// lang/de/enums.php
return [
    \App\Enums\UserRole::class => [
        'ADMIN'    => 'Administrator',
        'CUSTOMER' => 'Kunde',
    ],
];
```

The default lookup key is `'enums.'.static::class.'.'.$caseName`. To change the prefix or layout, override `getLocalizationName()` on the enum:

```php
enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;

    public static function getLocalizationName(): string
    {
        return 'roles.'.static::class;  // → roles.App\Enums\UserRole
    }

    // …
}
```

## Reading labels

```php
UserRole::ADMIN->name();              // 'Administrator' (translated for current locale)
UserRole::ADMIN->name;                // 'ADMIN' (PHP's built-in name — never translated)
UserRole::getLocalizedName('ADMIN');  // 'Administrator'
UserRole::getLocalizedName('UNKNOWN'); // 'UNKNOWN' (no translation → falls back to raw name)
UserRole::asSelectArray();            // [10 => 'Administrator', 50 => 'Customer'] (sorted by label)
UserRole::asServableEnum();           // [value => [name, value, localized_name]], keyed by value
```

### Switching locale at runtime

Translation is read on every call. Switching `app()->setLocale('de')` (or `App::setLocale('de')`) is enough — no need to clear any cache because there's none:

```php
app()->setLocale('de');
UserRole::ADMIN->name();              // 'Administrator'
```

## Edge cases

### Missing translation

Laravel's normal locale fallback applies, because `Lang::has()` and `__()` both consult the fallback locale:

1. The current locale's `enums.php` has the case → that label.
2. Otherwise the application's `fallback_locale` has it → the fallback label.
3. Neither has it → the raw case name (`'ADMIN'`).

```php
// lang/en/enums.php  (fallback_locale = en)
return [
    \App\Enums\UserRole::class => ['ADMIN' => 'Administrator', 'CUSTOMER' => 'Customer'],
];

// lang/de/enums.php
return [
    \App\Enums\UserRole::class => ['CUSTOMER' => 'Kunde'],
];
```

```php
app()->setLocale('de');
UserRole::CUSTOMER->name();  // 'Kunde'
UserRole::ADMIN->name();     // 'Administrator' (from the fallback locale)
```

There is no need to duplicate entries across locales to get a fallback.

### Translations without global helpers

The trait uses `Lang::has()` and `Lang::get()` directly. It does not require Laravel's global `__()`
or `app()` helpers, so the package keeps working when only its declared Illuminate components are
installed.

### Outside a Laravel app

Without an application or a resolved translator, `getLocalizedName()` short-circuits to the raw
value rather than throwing. A translator installed with `Lang::swap()` works without an application
container, which is useful in component-level tests. The translation lookup remains a convenience,
not a hard requirement.

## Shorter translation keys

If you'd rather key the lang file by the short class name than by the FQCN, override `getLocalizationName()`:

```php
public static function getLocalizationName(): string
{
    return 'enums.'.class_basename(static::class);  // enums.UserRole
}
```

Then place `lang/{locale}/enums.php` with `UserRole => [...]`. Slightly less robust if two enums share a basename in different namespaces — keep the default FQCN for production.

## Plural labels

If you need different labels for singular vs plural (e.g. "1 customer" / "2 customers"), the trait doesn't help directly. Add your own method on the enum and keep the plural strings in a separate lang file, so `name()` and `asSelectArray()` keep returning the plain singular label:

```php
enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;

    case ADMIN    = 10;
    case CUSTOMER = 50;

    public function pluralName(int $count): string
    {
        return trans_choice('enum_plurals.'.static::class.'.'.$this->name, $count);
    }
}
```

```php
// lang/en/enum_plurals.php
return [
    \App\Enums\UserRole::class => [
        'ADMIN'    => 'Administrator|Administrators',
        'CUSTOMER' => 'Customer|Customers',
    ],
];
```

Don't try to override `getLocalizedName()` and call `parent::getLocalizedName()` — the method comes from a trait, an enum has no parent class, and PHP rejects `parent::` there.
