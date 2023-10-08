<!--
id: readme
tags: ''
-->

# PHP Swap

## Summary

Provides a means to execute code under different PHP versions from the CLI. This was first built to run PhpUnit tests against Composer projects across different PHP versions. See example below.

## Quick Start

This simple code example should give you an idea of how this works.

```shell
mkdir foo
cd foo
composer init
composer require aklump/phpswap
php -v
./vendor/bin/phpswap use 5.6 "php -v; echo"
./vendor/bin/phpswap use 8.1 "php -v; echo"
```

## What It Does

* Temporarily modifies `$PATH` so that the requested PHP is used.
* Runs `composer update` so that dependencies are updated for the correct PHP version.
* Runs the given executable, which can be a command or a script path
* Restores the original PHP version and runs `composer update` once again to restore the original dependencies. (Unless `--no-composer-restore` is used.)

## What PHP Versions Are Supported?

To see the available versions, which will echo those versions provided by MAMP you can use the `show` command.

```bash
./vendor/bin/phpswap show
```

## Dependencies

* [MAMP](https://www.mamp.info/en/mamp)

## Getting Started

1. Ensure you have MAMP installed.
2. Download all PHP versions using MAMP that you hope to swap.
3. `composer require aklump/phpswap` in your project.
4. Use `vendor/bin/phpswap show` to see what versions are available.
5. `./phpswap list` to see all available commands.

## Examples with PhpUnit

Here is a pattern you can use to run PhpUnit under PHP 7.1, 7.4 and 8.1.

* Given you have installed phpunit in your project with Composer
* And you run your tests using `./vendor/bin/phpunit -c phpunit.xml`
* Then you can implement PhpSwap in the following way:

```shell
./vendor/bin/phpswap use 7.1 --no-composer-restore './vendor/bin/phpunit -c phpunit.xml'
./vendor/bin/phpswap use 7.4 --no-composer-restore './vendor/bin/phpunit -c phpunit.xml'
./vendor/bin/phpswap use 8.1 './vendor/bin/phpunit -c phpunit.xml'
```

## CLI Options

### `-v`

In verbose mode you will see the Composer output.

### `--no-composer-restore`

**Use this option to save time** if restoring the Composer dependencies is not necessary. In the PhpUnit examples above, it is not necessary to restore the dependencies after running against PHP 7.1, because the next line will install the dependencies necessary for testing against PHP 7.4. but the last line it's omitted because we want to restore the composer dependencies back to how they were originally.

When this option used, a file called _composer.lock.phpswap_ will remain in your project. It contains a copy of the _composer.lock_ file that was in your project before the first swap command. This file is used to replace _composer.lock_ at the end of a swap **without** the `--no-composer-restore` option. However when using this option it remains.

To quickly resolve this you can do something like `./vendor/bin/phpswap use 7.4 "echo"`  The PHP version is irrelevant in this case.

## Troubleshooting

If you try to run a command and see "Composer detected issues in your platform:", try running `composer update` then repeat your command.
