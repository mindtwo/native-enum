# Changelog

All notable changes to `native-enum` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## NEXT - YYYY-MM-DD

### Added
- **Public API:** Expose `BaseEnum::isLocalizable()` as a public capability check.
- **Documentation:** Add topic guides and a bundled coding-agent skill covering package usage.
- **CI:** Add GitHub Actions checks for tests and code style.

### Deprecated
- Nothing

### Fixed
- **Casts:** Correct collection serialization and comma parsing, preserve zero-valued cases,
  reject unsafe comma-stored strings, and validate cases received from other enum types.
- **Localization:** Fall back to raw names without a bound translator while supporting a
  translator swapped directly onto the `Lang` facade.
- **Livewire/JavaScript:** Accept arrays returned by `toLivewire()`, preserve numeric-string
  backing types in served enum data, and correct the published Blade component name.
- **Build:** Include the README-linked documentation in Composer distribution archives.

### Removed
- **CI:** Remove the obsolete Travis CI and Scrutinizer configurations.
- **Build:** Remove repository-only image assets from Composer distribution archives.

### Security
- Nothing
