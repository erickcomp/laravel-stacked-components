# Changelog

All notable changes to this package are documented in this file. It follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package follows [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Removed

- Support for Laravel 10 and 11. The package now requires Laravel 12 or 13; use 0.10 on older Laravel versions.

### Changed

- The distributed package no longer ships tests, CI and PHPUnit config, and the `Tests` namespace is no longer in the production autoload.

## [0.10.0] - 2026-09-28

The last release supporting Laravel 10 and 11.

### Fixed

- Asset functions given as array callables (`[Class::class, 'method']`) failed with a `TypeError`.
- Closures and invokable objects set as the asset function in the config failed with a `TypeError`.
- The default asset function, Laravel's `asset($path)`, failed on every `<x-js src>` and `<x-css src>`. Global functions with no `$asset` parameter now get the src as their first argument.
- The check against `src` together with inline code never fired.
- `<x-stacked-content>` and `<x-stacked-div>` pushed to a stack named `"false"` and ignored `once`.
- The component namespace (`<x-stacked::js>`) pointed to a namespace that doesn't exist, and `component-namespace = false` registered it anyway.
- Compiling any template with `</head>` left an output buffer open.
- `<x-css>` failed on Laravel 10.

### Changed

- The lookup of PHP's internal functions, done for every asset rendered, is now a hash lookup instead of a scan of ~1,900 names.
- `src` together with inline code now throws a `LogicException`.
- `component-namespace = false` no longer registers the `stacked` namespace.
- `Asset::phpInternalFunctions()` (protected) returns a map of name ⇒ index instead of a list.

### Added

- A test suite and CI on PHP 8.2–8.4 and Laravel 10–13.

[Unreleased]: https://github.com/erickcomp/laravel-stacked-components/compare/0.10.0...HEAD
[0.10.0]: https://github.com/erickcomp/laravel-stacked-components/compare/0.9.1...0.10.0
