# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
