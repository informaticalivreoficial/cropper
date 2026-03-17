# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2024-03-17

### Changed
- Namespace changed from `CoffeeCode\Cropper` to `Renato\Cropper`
- Minimum PHP version bumped to 8.3
- Constructor refactored using PHP 8 promoted properties
- Replaced `WebPConvert` dependency with native GD WebP support
- `make()` now returns `null` instead of error strings on failure
- `imageCache()` refactored using `match` expression
- `flush()` refactored using `str_contains`
- Removed deprecated `imagedestroy()` calls (PHP 8.3)

### Removed
- Dependency `rosell-dk/webp-convert` removed in favor of native GD

### Added
- PHPUnit 11 test suite with 13 tests
- `autoload-dev` configuration for tests

## [1.x] - Original

- Original package by [Robson V. Leite](https://github.com/robsonvleite)
- See: https://github.com/robsonvleite/cropper