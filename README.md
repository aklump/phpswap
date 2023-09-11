# PHP Swap

## Summary

Provides a means to execute code under different PHP versions from the CLI. This was first built to run PhpUnit tests against Composer projects across different PHP versions. See example below.

## Quick Start

This throw-away example will give you an idea of how this works.

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
* Restores the original PHP version and runs `composer update` once again to restore the original dependencies.

## Dependencies

* MAMP

## Getting Started

1. Ensure you have MAMP installed.
2. Download all PHP versions using MAMP that you hope to swap.
3. `composer require aklump/phpswap` in your project.
4. Use `vendor/bin/phpswap show` to see what versions are available.
5. `./phpswap list` to learn more.

## Examples with PhpUnit

Here is a pattern you can use to run PhpUnit under PHP 7.1, 7.4 and 8.1.

* Given you have installed phpunit in your project with Composer
* And you run your tests using `./vendor/bin/phpunit -c phpunit.xml`
* Then you can implement PhpSwap in the following way:

```shell
./vendor/bin/phpswap use 7.1 './vendor/bin/phpunit -c phpunit.xml'
./vendor/bin/phpswap use 7.4 './vendor/bin/phpunit -c phpunit.xml'
./vendor/bin/phpswap use 8.1 './vendor/bin/phpunit -c phpunit.xml'
```

## Contributing

If you find this project useful... please [donate](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4E5KZHDQCEUV8&item_name=Contribution%20to%20aklump%2Fphpswap).
