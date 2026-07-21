<!--
id: testing
tags: ''
-->

# Multi-PHP Testing

PhpSwap is incredibly powerful for testing a project across different PHP versions. It handles the complexity of swapping the PHP version, updating Composer dependencies for that version, running your tests, and then restoring everything to the original state.

## Automated Composer Updates

One of the most powerful features of PhpSwap is its ability to manage Composer dependencies during a version swap. When you use PhpSwap to run a command:

1. It detects if a `composer.json` file is present in the working directory.
2. If found, it safely moves your current `composer.lock` to a temporary lock file.
3. It runs `composer update` using the **newly selected PHP version**. This ensures that the dependencies installed are compatible with that specific PHP version.
4. It executes your test command (e.g., `phpunit`).
5. After the command finishes, it restores your original `composer.lock` and runs `composer update` with the **original PHP version** to return your project to its initial state.

This allows you to verify that your project works correctly across all supported PHP versions without manually managing your `vendor` directory.

## Using `phpswap_execute.php`

For automated testing, you should use `phpswap_execute.php`. This is a lightweight front-controller designed to be called from test runner scripts.

### Checking for Version Support

You can check if a specific PHP version is available on the system using the `supports` command. It will exit with `0` if the version is found, and `1` otherwise.

```bash
if ./phpswap_execute.php supports 8.2; then
  ./phpswap_execute.php using 8.2 './vendor/bin/phpunit'
fi
```

### Basic Usage

```shell script
phpswap_execute.php using 8.1 './vendor/bin/phpunit'
```

### In a Test Runner Script

You can create a script to run your tests across multiple versions.

```bash
#!/usr/bin/env bash

# Use phpswap_execute.php to run tests across multiple versions
./phpswap_execute.php using 8.1 './vendor/bin/phpunit'
./phpswap_execute.php using 8.2 './vendor/bin/phpunit'
./phpswap_execute.php using 8.3 './vendor/bin/phpunit'
./phpswap_execute.php using 8.4 './vendor/bin/phpunit'
```

## Example: Boilerplate Test Runner

Below is a more robust example of a test runner script that includes error handling and verbosity.

```bash
#!/usr/bin/env bash
# Resolve the directory of this script
x(){ echo "No script dir" >&2;return 1 2>/dev/null||exit 1;};if [ -n "${BASH_VERSION:-}" ];then s="${BASH_SOURCE[0]}";elif [ -n "${ZSH_VERSION:-}" ];then eval 's="${(%):-%x}"';else x;fi;[ -n "$s" ]||x;while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")"&&pwd)"||x;s="$(readlink "$s")"||x;[[ $s != /* ]]&&s="$d/$s";done;__DIR__="$(cd -P "$(dirname "$s")"&&pwd)"||x;unset s d;unset -f x

cd "$__DIR__/.."

function failed() {
  echo -e "\033[1m\033[48;5;226m$1\033[0m"
}

# Check for required files
if ! [ -e ./phpswap_execute.php ] || ! [ -e ./vendor/bin/phpunit ]; then
    failed "Missing phpswap_execute.php or phpunit. Please check your installation."
    exit 1
fi

verbose=''
if [[ "${*}" == *'-v'* ]]; then
  verbose='-v'
fi

# Run tests across multiple versions
versions=("8.1" "8.2" "8.3" "8.4")

for version in "${versions[@]}"; do
  echo "Testing PHP $version..."
  if ! ./phpswap_execute.php use $version $verbose './vendor/bin/phpunit'; then
    failed "PHP $version tests failed."
    exit 1
  fi
done

echo "All tests passed!"
```

## Testing with a Local Server

If your tests require a running web server (e.g., for integration tests), you can still use PhpSwap.

```bash
#!/usr/bin/env bash

# ... setup logic ...

# Start the server using a specific PHP version
./phpswap_execute.php using 8.1 'php -S 127.0.0.1:8080 -t public & sleep 2; ./vendor/bin/phpunit; kill $!'
```

By combining PhpSwap with your existing test suite, you can ensure high compatibility and reliability across the entire PHP ecosystem.
