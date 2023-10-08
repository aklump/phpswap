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
* Restores the original PHP version and runs `composer install` to restore the original dependencies in _/vendor/_.

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
* See also Controller File Example further down.

```shell
./vendor/bin/phpswap use 7.1 './vendor/bin/phpunit -c phpunit.xml'
./vendor/bin/phpswap use 7.4 './vendor/bin/phpunit -c phpunit.xml'
./vendor/bin/phpswap use 8.1 './vendor/bin/phpunit -c phpunit.xml'
```

## CLI Options

### `-v`

In verbose mode you will see the Composer output.

### `--working-dir`

This sets the working directory from which your script is called.  This is optional.

## Troubleshooting

During execution, a file called _{{ swapfile }}_ is temporarily created in your project. It contains a copy of the _composer.lock_ file that was in your project before the swap. This file is used to refresh _composer.lock_ at the end of a swap. In some error situations this file may not be deleted. Use the snippet below to recover.

You may also see "Composer detected issues in your platform:" after a swap executed. The same applies here, try the snippet below.

```shell
mv {{ swapfile }} composer.lock;composer update
```

## Controller File Example

Here is a complete snippet for controlling tests. Save as _bin/run_unit_tests.sh_ and call it like this: `bin/run_unit_tests.sh -v`. You may leave off the verbose `-v` flag unless troubleshooting.

```bash
#!/usr/bin/env bash
s="${BASH_SOURCE[0]}";[[ "$s" ]] || s="${(%):-%N}";while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")" && pwd)";s="$(readlink "$s")";[[ $s != /* ]] && s="$d/$s";done;__DIR__=$(cd -P "$(dirname "$s")" && pwd)

cd "$__DIR__/.."

verbose=''
if [[ "${*}" == *'-v'* ]]; then
  verbose='-v'
fi
./vendor/bin/phpswap use 7.3 $verbose './vendor/bin/phpunit -c tests_unit/phpunit.xml'
./vendor/bin/phpswap use 7.4 $verbose './vendor/bin/phpunit -c tests_unit/phpunit.xml'
./vendor/bin/phpswap use 8.0 $verbose './vendor/bin/phpunit -c tests_unit/phpunit.xml'
./vendor/bin/phpswap use 8.1 $verbose './vendor/bin/phpunit -c tests_unit/phpunit.xml'
./vendor/bin/phpswap use 8.2 $verbose './vendor/bin/phpunit -c tests_unit/phpunit.xml'
```
