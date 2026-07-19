<!--
id: mamp
tags: ''
-->

# MAMP PHP Provider

PhpSwap can discover PHP versions bundled with MAMP and switch the current shell session to them.

## What it does

PhpSwap scans your MAMP installation to find available PHP versions. This allows you to easily switch between different PHP versions provided by MAMP for your development projects.

## Installing MAMP

You can download and install MAMP from the official website:

[https://www.mamp.info/en/mamp/mac/](https://www.mamp.info/en/mamp/mac/)

## Expected path

The MAMP provider expects to find MAMP at the following location:

`/Applications/MAMP`

PHP versions should be located under:

`/Applications/MAMP/bin/php`

Example path to a PHP binary:

`/Applications/MAMP/bin/php/php8.4.11/bin/php`

## Checking installed MAMP PHP versions

You can see which PHP versions are available in MAMP by listing the contents of the MAMP PHP directory:

```shell script
ls /Applications/MAMP/bin/php
```

Example output:

```plain text
php8.2.29
php8.3.24
php8.4.11
```

You can check the version of a specific MAMP PHP binary:

```shell script
/Applications/MAMP/bin/php/php8.4.11/bin/php -v
```

## Using with PhpSwap

Once MAMP is installed, its PHP versions will be available in PhpSwap:

```shell script
phpswap show
phpswap --set
phpswap --save
```

## Diagnosing PHP binaries

Run:
```shell script
phpswap diagnose
```
This checks discovered MAMP PHP binaries by running `php -v`.

If a MAMP PHP binary fails, verify it directly:
```shell script
/Applications/MAMP/bin/php/php8.4.11/bin/php -v
```

## Provider priority

MAMP has lower priority than Homebrew. If both MAMP and Homebrew provide the same exact PHP version, PhpSwap will prioritize the Homebrew version. MAMP versions will still appear in `phpswap show` as long as they are not overridden by a higher-priority provider with the same version.

## Troubleshooting

### MAMP cannot be found

Ensure MAMP is installed at the expected path: `/Applications/MAMP`.

### Missing PHP directory

Check that the PHP directory exists within MAMP: `/Applications/MAMP/bin/php`.

### Version does not appear

If a version doesn't appear in PhpSwap, verify it exists in the MAMP directory:

```shell script
ls /Applications/MAMP/bin/php
```
