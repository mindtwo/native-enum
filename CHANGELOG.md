# Changelog

All notable changes to `native-enum` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - YYYY-MM-DD

### Added
- Nothing

### Changed
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing

## 2.0 - 2026-11-20

### Added
- **Public API:** Expose `BaseEnum::isLocalizable()` as a public capability check.
- **Documentation:** Add topic guides and a bundled coding-agent skill covering package usage.
- **CI:** Add GitHub Actions checks for tests and code style.

### Changed
These entries change behaviour that existing code may rely on. Each one names what to adjust.

- **Breaking, casts:** A case of another enum is rejected with `ItemNotFoundException`, even if
  its value matches a case of the cast's enum. Earlier releases stored it unchecked. Assign a
  case of the cast's enum or a raw value.
- **Breaking, casts:** A `:collection` attribute stored as JSON throws `UnexpectedValueException`
  when the column holds anything but a JSON array. Earlier releases read invalid JSON and a JSON
  `null` as an empty list and wrapped a JSON scalar into a list. An empty string still reads as
  an empty list. Repair such rows before upgrading.
- **Breaking, casts:** `:collection,comma` throws `InvalidArgumentException` when writing a
  string value that is empty, contains a comma or has surrounding whitespace. Use the default
  JSON storage for enums with such values.
- **Breaking, helpers:** Non-strict `hasValue()` and the non-strict `EnumValue` rule compare ints
  and strings only. `true`, `1.0` and `null` no longer match the values `1`, `1` and `''`.
- **Breaking, Livewire:** `fromLivewire()` throws `InvalidArgumentException` for a payload
  without an int or string `value`. Earlier releases raised a warning and resolved such a
  payload to the case with the value `0` or `''` if the enum had one.
- **Breaking, JavaScript:** `asServableEnum()` and `<x-m2-enums::serve />` keep the string type
  of numeric-string backing values, so `value` is `"10"` where it used to be `10`. Compare
  against the string in JavaScript.

### Deprecated
- Nothing

### Fixed
- **Casts:** Correct comma serialization and parsing, preserve zero-valued cases, and store an
  array with gaps in its keys as a JSON list instead of a JSON object.
- **Validation:** `EnumName` and non-strict `EnumValue` fail validation for array input instead
  of raising an error.
- **Localization:** Fall back to raw names without a bound translator while supporting a
  translator swapped directly onto the `Lang` facade.
- **Livewire/JavaScript:** Accept arrays returned by `toLivewire()` and correct the published
  Blade component name.
- **Build:** Include the README-linked documentation in Composer distribution archives.

### Removed
- **Breaking, helpers:** Remove `__invoke()` from `BaseEnum`, so cases are no longer callable.
  Laravel's collection methods treat a callable argument as a callback, which made
  `collect($roles)->contains(UserRole::ADMIN)` return a wrong result. Replace `$role()` with
  `$role->value`. The static shortcut `UserRole::ADMIN()` is unchanged.
- **CI:** Remove the obsolete Travis CI and Scrutinizer configurations.
- **Build:** Remove repository-only image assets from Composer distribution archives.

### Security
- Nothing
