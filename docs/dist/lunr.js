var lunrIndex = [{"id":"changelog","title":"Changelog","body":"All notable changes to this project will be documented in this file.\n\nThe format is based on [Keep a Changelog](https:\/\/keepachangelog.com\/en\/1.0.0\/),\nand this project adheres to [Semantic Versioning](https:\/\/semver.org\/spec\/v2.0.0.html).\n\n## [Unreleased]\n\n## [0.0.3] - 2023-10-08\n\n### Removed\n- The `no-composer-restore` option due to it being too fragile."},{"id":"readme","title":"PHP Swap","body":"## Summary\n\nProvides a means to execute code under different PHP versions from the CLI. This was first built to run PhpUnit tests against Composer projects across different PHP versions. See example below.\n\n## Quick Start\n\nThis simple code example should give you an idea of how this works.\n\n```shell\nmkdir foo\ncd foo\ncomposer init\ncomposer require aklump\/phpswap\nphp -v\n.\/vendor\/bin\/phpswap use 5.6 \"php -v; echo\"\n.\/vendor\/bin\/phpswap use 8.1 \"php -v; echo\"\n```\n\n## What It Does\n\n* Temporarily modifies `$PATH` so that the requested PHP is used.\n* Runs `composer update` so that dependencies are updated for the correct PHP version.\n* Runs the given executable, which can be a command or a script path\n* Restores the original PHP version and runs `composer install` to restore the original dependencies in _\/vendor\/_.\n\n## What PHP Versions Are Supported?\n\nTo see the available versions, which will echo those versions provided by MAMP you can use the `show` command.\n\n```bash\n.\/vendor\/bin\/phpswap show\n```\n\n## Dependencies\n\n* [MAMP](https:\/\/www.mamp.info\/en\/mamp)\n\n## Getting Started\n\n1. Ensure you have MAMP installed.\n2. Download all PHP versions using MAMP that you hope to swap.\n3. `composer require aklump\/phpswap` in your project.\n4. Use `vendor\/bin\/phpswap show` to see what versions are available.\n5. `.\/phpswap list` to see all available commands.\n\n## Examples with PhpUnit\n\nHere is a pattern you can use to run PhpUnit under PHP 7.1, 7.4 and 8.1.\n\n* Given you have installed phpunit in your project with Composer\n* And you run your tests using `.\/vendor\/bin\/phpunit -c phpunit.xml`\n* Then you can implement PhpSwap in the following way:\n* See also Controller File Example further down.\n\n```shell\n.\/vendor\/bin\/phpswap use 7.1 '.\/vendor\/bin\/phpunit -c phpunit.xml'\n.\/vendor\/bin\/phpswap use 7.4 '.\/vendor\/bin\/phpunit -c phpunit.xml'\n.\/vendor\/bin\/phpswap use 8.1 '.\/vendor\/bin\/phpunit -c phpunit.xml'\n```\n\n## CLI Options\n\n### `-v`\n\nIn verbose mode you will see the Composer output.\n\n### `composer.lock.phpswap`\n\nDuring execution, a file called _composer.lock.phpswap_ is temporarily created in your project. It contains a copy of the _composer.lock_ file that was in your project before the first swap. This file is used to refresh _composer.lock_ at the end of a swap. In some error situations this file may not be deleted. To recover run the following `.\/vendor\/bin\/phpswap use 7.4 \"echo\"`  The PHP version is irrelevant in this case.\n\nIf that doesn't work copy the contents of _composer.lock.phpswap_ over _composer.lock_ and delete _composer.lock.phpswap_.\n\n## Troubleshooting\n\nIf you try to run a command and see \"Composer detected issues in your platform:\", try running `composer install` (or `composer update`?) then repeat your command.\n\n## Controller File Example\n\nHere is a complete snippet for controlling tests. Save as _bin\/run_unit_tests.sh_ and call it like this: `bin\/run_unit_tests.sh -v`. You may leave off the verbose `-v` flag unless troubleshooting.\n\n```bash\n#!\/usr\/bin\/env bash\ns=\"${BASH_SOURCE[0]}\";[[ \"$s\" ]] || s=\"${(%):-%N}\";while [ -h \"$s\" ];do d=\"$(cd -P \"$(dirname \"$s\")\" && pwd)\";s=\"$(readlink \"$s\")\";[[ $s != \/* ]] && s=\"$d\/$s\";done;__DIR__=$(cd -P \"$(dirname \"$s\")\" && pwd)\n\ncd \"$__DIR__\/..\"\n\nverbose=''\nif [[ \"${*}\" == *'-v'* ]]; then\n  verbose='-v'\nfi\n.\/vendor\/bin\/phpswap use 7.3 $verbose '.\/vendor\/bin\/phpunit -c tests_unit\/phpunit.xml'\n.\/vendor\/bin\/phpswap use 7.4 $verbose '.\/vendor\/bin\/phpunit -c tests_unit\/phpunit.xml'\n.\/vendor\/bin\/phpswap use 8.0 $verbose '.\/vendor\/bin\/phpunit -c tests_unit\/phpunit.xml'\n.\/vendor\/bin\/phpswap use 8.1 $verbose '.\/vendor\/bin\/phpunit -c tests_unit\/phpunit.xml'\n.\/vendor\/bin\/phpswap use 8.2 $verbose '.\/vendor\/bin\/phpunit -c tests_unit\/phpunit.xml'\n```"}]