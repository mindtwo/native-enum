# Native Enum

`mindtwo/native-enum` brings PHP's native enums into Laravel-flavoured convenience: validation rules, Eloquent casts (including nullable and collections), localization, JS serving, and Livewire support. One `use BaseEnum;` and your enum is ready for Laravel.

New here? Start with [Get started](get-started.md). The [README](https://github.com/mindtwo/native-enum#readme) has the short version of everything on one page.

## Pages

| Page | Covers |
| --- | --- |
| [Get started](get-started.md) | Requirements, installation, publishable assets, your first enum |
| [Creating enums](creating-enums.md) | `make:enum`, namespaces, custom stubs, writing an enum by hand |
| [Helper methods](helpers.md) | Every method `BaseEnum` adds, with return values |
| [Validation](validation.md) | `EnumValue`, `EnumName`, `Enum`, string syntax, error messages |
| [Eloquent casts](casts.md) | Single values, `:nullable`, `:collection`, `:collection,comma` |
| [Localization](localization.md) | `LocalizedEnum`, lang files, translated labels |
| [Livewire](livewire.md) | Enum properties on components |
| [Serving enums to JavaScript](javascript.md) | `<x-m2-enums::serve />` and `window.m2_ServedEnums` |

## Why this package exists

Before PHP had native enums, the popular workaround was `BenSampo/laravel-enum` and similar libraries that shipped `MyEnum::Admin` constants on a regular class. Native PHP enums (`enum UserRole: int { case Admin = 1; }`) are stronger: they're proper types, integrate with `match`, and can implement interfaces — but they're awkward inside Laravel without a few glue methods. `BaseEnum` adds those glue methods without taking over the enum.
