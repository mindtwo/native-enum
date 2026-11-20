# BaseEnum helper methods

All methods are defined on the trait. They're inherited by every enum that uses `BaseEnum`.

## Bulk accessors

### `getValues($names = null): array`

```php
UserRole::getValues();                  // [10, 50]
UserRole::getValues('ADMIN');           // [10]
UserRole::getValues(UserRole::ADMIN);   // [10]
UserRole::getValues(['ADMIN', 'CUSTOMER']); // [10, 50]
```

If `$names` is null, returns all values in declaration order. Otherwise `Arr::wrap()` accepts any mix of strings (case names) or case instances.

### `getNames($values = null): array`

```php
UserRole::getNames();                   // ['ADMIN', 'CUSTOMER']
UserRole::getNames(10);                 // ['ADMIN']
UserRole::getNames([10, 50]);           // ['ADMIN', 'CUSTOMER']
```

### `asArray(): array` / `asCollection(): Collection`

```php
UserRole::asArray();
// [10 => 'ADMIN', 50 => 'CUSTOMER']

UserRole::asCollection();
// Illuminate\Support\Collection with the same shape
```

The `value => name` shape is the most common: easy to drop into a `<select>` once you map the names through `getLocalizedName()`.

### `asSelectArray(): array`

```php
UserRole::asSelectArray();
// [10 => 'Administrator', 50 => 'Customer']   ← localized, sorted by label
```

Sorted ascending by **label** (`Collection::sort()` sorts the labels and keeps the value keys), so the key order can differ from the declaration order. Falls back to the raw case name for non-localized enums or untranslated cases.

### `asServableEnum(): array`

```php
UserRole::asServableEnum();
// [
//   10 => ['name' => 'ADMIN',    'value' => 10, 'localized_name' => 'Administrator'],
//   50 => ['name' => 'CUSTOMER', 'value' => 50, 'localized_name' => 'Customer'],
// ]
```

Keyed by case value and sorted by case name. Because of the keys it JSON-encodes as an **object** (`{"10": {…}, "50": {…}}`), not an array — unless the values are consecutive ints starting at `0`. This is what the `<x-m2-enums::serve />` component emits per enum; iterate it in JS with `Object.values()`.

## Single-value lookups

### `getInstance($value): self`

```php
UserRole::getInstance(10);              // UserRole::ADMIN
UserRole::getInstance('10');            // throws ItemNotFoundException (strict ===)
```

Strict comparison on `->value`. Throws `Illuminate\Support\ItemNotFoundException` for unknown values and for values of the wrong type. If the input is a string (request data), convert it first: `UserRole::getInstance((int) $value)` or PHP's own `UserRole::from((int) $value)`.

### `getName($value): string`

```php
UserRole::getName(10);                  // 'ADMIN'
```

Strict. Throws `ItemNotFoundException` on unknown.

### `getValue(self|string $name): int|string`

```php
UserRole::getValue('ADMIN');            // 10
UserRole::getValue(UserRole::ADMIN);    // 10
```

The `$name` argument accepts both a string (case name) and a case instance. Useful when forwarding user input that may already be normalized.

### `hasValue($value, bool $strict = true): bool` / `hasName(string $name): bool`

```php
UserRole::hasValue(10);                 // true
UserRole::hasValue('10');               // false (strict by default)
UserRole::hasValue('10', false);        // true  (coerced)
UserRole::hasName('ADMIN');             // true
UserRole::hasName('admin');             // false (case-sensitive)
```

Strict mode uses PHP's `in_array(..., $strict=true)`. Non-strict projects both sides through `strval()` — fine for ints, marginal for strings.

## Random selection

```php
UserRole::getRandomValue();             // int
UserRole::getRandomName();              // string
UserRole::getRandomInstance();          // UserRole case
```

All three use `array_rand()`. **Not cryptographically secure** — don't use them to generate tokens, password reset codes, or IDs. For secure randomness, use `random_int()`.

## Case invocation

### `$case()` — `__invoke`

```php
(UserRole::ADMIN)();                    // 10
```

PHP's `__invoke` lets you call a case like a function. The trait's `__invoke` returns `$case->value`. Useful in array_map / pipeline contexts where you want the primitive without typing `->value` everywhere.

### `static::CASENAME()` — `__callStatic`

```php
UserRole::ADMIN();                      // 10
UserRole::CUSTOMER();                   // 50
UserRole::DOES_NOT_EXIST();             // throws UndefinedCaseError
```

`BaseEnum` defines `__callStatic()` that looks up cases by name and returns the value. Calling a non-existent case throws `mindtwo\NativeEnum\Exceptions\UndefinedCaseError` with PHP's native wording ("Undefined constant Foo::Bar") so existing log scrapers don't need updating.

⚠️ **The `() ` after a case name does NOT return the case** — it returns the primitive value. If you want the case instance, use `UserRole::ADMIN` (no parens).

## Localization helpers

See [localized.md](localized.md) for the full picture. The methods:

| Method                                  | Purpose                                   |
| --------------------------------------- | ----------------------------------------- |
| `isLocalizable()`                       | `true` iff `implements LocalizedEnum`     |
| `getLocalizationName()`                 | Default key: `'enums.'.static::class`     |
| `getLocalizedName(string $name)`        | Translated label or raw name fallback     |
| `$case->name()`                         | Same as `getLocalizedName($case->name)`   |

## Eloquent / serialization hooks

| Method                              | Purpose                                              |
| ----------------------------------- | ---------------------------------------------------- |
| `castUsing(array $args)`            | Returns `EnumCast` or `EnumCollectionCast` instance  |
| `static::fromLivewire($value)`      | Accepts a case, the `toLivewire()` array, or an object with `value` |
| `$case->toLivewire()`               | Returns `['value' => …, 'label' => …]`               |

`castUsing()` is only called by Laravel when the enum implements `Castable` — see [casts.md](casts.md).

The Livewire methods are plain helpers. Livewire 3 hydrates backed enums natively and never calls them unless the enum implements `Livewire\Wireable`, which it doesn't need. See [livewire.md](livewire.md).
