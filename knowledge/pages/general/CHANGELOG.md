<!--
id: changelog
tags: ''
-->

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added `supports` command to `phpswap_execute.php` to check for PHP version availability.
- `phpswap diagnose` command to identify broken PHP binaries.
- Homebrew PHP provider.
- JSON shell-action contract for safe shell mutation.
- `phpswap status --verbose` to show raw environment variables.
- Persistent caching of PHP provider discovery, making repeated `supports`/`using` calls fast. The cache self-invalidates when a PHP version is added, removed or upgraded.
- `--flush` flag (usable with both `phpswap` and `phpswap_execute.php`) to clear the provider cache.

### Changed
- Complete rebuild of the CLI architecture around a central PHP controller and thin Bash adapter.
- `phpswap --save` is now non-interactive and saves the currently active PHP version.
- `phpswap status` now provides a cleaner, more concise summary.
- Standardized terminology: swapped, saved, swap file, default PHP, provider.
- Improved Homebrew PHP discovery including Cellar scanning and broken binary detection.
- Refactored `phpswap --delete` to always unset the session and restore default PHP.

### Removed
- Legacy Bash-based command implementations in `phpswap.sh`.
- Terminology: shim, persistent file.

## [0.0.13] - 2025-05-10

### Added

- Autoswap feature

### Changed

- Command `pick` has been changed to `set`
- 🚨BREAKING CHANGE! Installation method... see phpswapcli.md for more info.
- 🚨BREAKING CHANGE! If you see "....phpswap:1: command not found: 8.2.26", you must re-register, e.g. `phpswap set`

### Removed

- Need to paste command to swap php, it now happens automatically.

## [0.0.10] - 2024-12-02

### Added

- Able to find last version from child directories of a project.

## [0.0.9] - 2024-12-02

### Added

- Remembers the last version used in the CLI command for faster switching (per directory).
- Unit test coverage.

### Changed

- Moved some classes to new subdirectories.

## [0.0.3] - 2023-10-08

### Removed

- The `no-composer-restore` option due to it being too fragile.
