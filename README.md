# PhpSwap

![PhpSwap](images/phpswap.jpg)

## Summary

**PhpSwap** is a package for working with multiple PHP versions. Its CLI lets you switch between installed PHP versions by updating `PATH` in your current shell session, without changing your system-wide PHP configuration.

PhpSwap can also run automated tests across multiple PHP versions, with built-in **Composer** dependency management. Its core workflow is portable across Unix-like shells, while current provider support focuses on Homebrew and MAMP installations. PHP binaries are discovered through a pluggable provider architecture, making it easy to add support for other installation methods later.

## Quick Start

```shell
# Show available versions
phpswap show

# Interactively swap for the current session
phpswap --set

# Save the current PHP version for a project
phpswap --save

# Unset and return to default PHP
phpswap --unset
```

## What It Does

* Temporarily modifies `$PATH` with a different PHP version binary.
* Uses a JSON shell-action contract to safely mutate the current shell environment.
* Discovers PHP versions from multiple providers like Homebrew and MAMP.
* **Multi-PHP Testing**: Easily run your test suite across multiple PHP versions with automated Composer dependency management.

## Multi-PHP Testing

One of PhpSwap's most powerful features is the ability to test your project against multiple PHP versions. When running a command through PhpSwap, it can automatically update your Composer dependencies for the target PHP version and restore them afterwards.

```bash
./phpswap_execute.php using 8.1 './vendor/bin/phpunit'
```

See [Multi-PHP Testing](@testing) for more details.

### Checking for Version Support

You can check if a version is supported before running a command:

```bash
if ./phpswap_execute.php supports 8.1; then
  ./phpswap_execute.php using 8.1 './vendor/bin/phpunit'
fi
```

## What PHP Versions Are Supported?

To see the available versions, use the `show` command.

```bash
phpswap show
```

## Providers

PhpSwap discovers PHP binaries using providers. Currently supported providers:
- **Homebrew**: Discovers PHP versions installed via Homebrew formulae.
- **MAMP**: Discovers PHP versions bundled with MAMP.

## Dependencies

* Bash or Zsh
* PHP (for the controller)
* (Optional) Homebrew or MAMP for managing PHP versions.

## Troubleshooting

Use the `diagnose` command to check if discovered PHP binaries are broken or missing dependencies.

```shell
phpswap diagnose
```
