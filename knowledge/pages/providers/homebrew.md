<!--
id: homebrew
tags: ''
-->

# Homebrew PHP Provider

PhpSwap can discover PHP versions installed by Homebrew and switch the current shell session to them.

Homebrew PHP versions are discovered from the following locations:

- `/opt/homebrew/opt`
- `/usr/local/opt`
- `/opt/homebrew/Cellar`
- `/usr/local/Cellar`

## Installing Homebrew

If you don't have Homebrew installed, you can install it using the following command:

```shell script
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Follow Homebrew's post-install shell setup instructions to ensure it's correctly added to your PATH.

## Installing PHP versions with Homebrew

You can install multiple PHP versions using Homebrew. Here are some examples:

```shell script
brew install php@8.3
brew install shivammathur/php/php@5.6-debug
```

- Availability depends on your Homebrew taps and Homebrew's current formula support.
- Use `brew search php` to see available versions
- Add older versions with `brew tap shivammathur/php`

## Checking installed versions

To see which PHP formulae you have installed:

```shell script
brew list | grep '^php'
```

You can also find the installation prefix for a specific formula:

```shell script
brew --prefix php
brew --prefix php@8.3
```

Directly check the version of a Homebrew PHP binary:

```shell script
/opt/homebrew/opt/php/bin/php -v
/opt/homebrew/opt/php@8.3/bin/php -v
```

On Intel Macs, use the `/usr/local` prefix:

```shell script
/usr/local/opt/php/bin/php -v
/usr/local/opt/php@8.3/bin/php -v
```

## Using with PhpSwap

Once installed, Homebrew PHP versions will be available in PhpSwap:

```shell script
phpswap show
phpswap --set
phpswap --save
phpswap status
```

`phpswap show` displays the PHP versions PhpSwap can use.

## Provider priority

PhpSwap shows one row per PHP version. If the same version is available from more than one provider, the provider with the highest priority wins. The Homebrew provider has higher priority than the MAMP provider, so Homebrew is selected for duplicate versions.

## Diagnosing broken PHP binaries

Older Homebrew PHP versions may be installed but fail to run because they depend on dynamic libraries that are no longer installed. This is common with legacy PHP versions and OpenSSL.

Run:
```shell script
phpswap diagnose
```
This checks each PHP binary discovered by PhpSwap using:
```shell script
php -v
```
If a binary fails, PhpSwap prints the failure output.

Example failure:
```plain text
dyld: Library not loaded: /usr/local/opt/openssl/lib/libcrypto.1.0.0.dylib
```
If this happens, reinstalling the formula, installing missing dependencies, or using another provider such as MAMP may be required.

## Troubleshooting

### PHP binary is installed but fails to run

Run:
```shell script
phpswap diagnose
```
Then test the binary directly:
```shell script
/usr/local/opt/php@7.1/bin/php -v
```
Legacy PHP versions may depend on libraries that are no longer available on your system.

### `phpswap show` does not list Homebrew PHP

- Ensure the formulae are actually installed: `brew list | grep '^php'`.
- Check the `opt` and `Cellar` directories:
  ```shell script
  ls -la /opt/homebrew/opt | grep php
  ls -la /usr/local/opt | grep php
  ls -la /opt/homebrew/Cellar | grep php
  ls -la /usr/local/Cellar | grep php
  ```
- Verify the binary exists and is executable:
  ```shell script
  /opt/homebrew/opt/php@8.3/bin/php -r 'echo PHP_VERSION;'
  ```

### Formula not available

Older PHP formulae may be unavailable in core Homebrew. You might need to use additional taps if they are supported.

### Apple Silicon vs Intel paths

- `/opt/homebrew` is the standard prefix for Apple Silicon (M1/M2/M3) Macs.
- `/usr/local` is the standard prefix for Intel Macs.
