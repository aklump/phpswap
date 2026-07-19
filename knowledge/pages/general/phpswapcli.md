<!--
id: phpswapcli
tags: ''
-->

# PhpSwap CLI

**PhpSwap** is a developer tool for macOS that allows you to instantly switch between different CLI PHP versions. Unlike other tools that change your system's global state, PhpSwap's changes are scoped exclusively to your current terminal session. It automatically discovers PHP binaries from popular sources like **Homebrew** and **MAMP**, and provides a powerful execution engine for automated, multi-version PHP testing with built-in **Composer** dependency management.

## Installing `phpswap` CLI

Add an alias, which sources the `phpswap.sh` script, adjusting the path as appropriate.

```shell
alias phpswap="source ~/Code/Packages/cli/phpswap/app/phpswap.sh"
```

## Usage

For help type `phpswap -h`

### `phpswap show`

Lists all available PHP versions and their binary paths discovered from registered providers.

### `phpswap status`

Shows the currently active PHP version, binary path, and whether the session is currently swapped. It also indicates if a swap file is saved and active.

### `phpswap diagnose`

Scans all discovered PHP binaries and reports any that fail to execute correctly.

### `phpswap`

If a swap file is found in the current directory or a parent directory, PhpSwap will apply it. If no swap file is found, it behaves like `phpswap --set`.

### `phpswap --set` (Temporary Session Swap)

Interactively select a PHP version for the current shell session only. This does not create a swap file.

```shell
phpswap --set
```

### `phpswap --save` (Register a Project's PHP Version)

1. In your terminal, `cd` to your project's root directory.
2. Ensure the PHP version you want to save is currently active (e.g., by using `phpswap --set`).
3. Type `phpswap --save`
4. Test it with `php -v`

This will create a `.phpswap` swap file in the current directory, persisting the currently active PHP version.

### `phpswap --unset` (Resetting the PATH)

If you want to return to your default PHP version, you can run:

```shell script
phpswap --unset
```

or the legacy command:

```shell script
phpswap reset
```

### `phpswap --delete` (Delete a Project's Configuration)

To delete the swap file and restore the default PHP for the current session:

```shell
phpswap --delete
```

## Configuration

PhpSwap is configured via `phpswap.config.php` located in the application root. If this file is missing, you can create it by running `./phpswap-repair.sh`.

### Runtime PHP

The runtime PHP is the PHP version used by PhpSwap itself to run its internal controller. This is managed automatically by `phpswap-repair.sh`, which writes the following to your config:

```php
$config->setRuntimePhp('/path/to/php/bin/php');
```

`phpswap-repair.sh` will only update this line if it is currently empty or points to an invalid PHP binary. This allows you to manually override the runtime PHP if needed.

### Providers

You can control which PHP providers are used and their priority order in `phpswap.config.php`:

```php
$config->addPhpProvider(new \AKlump\PhpSwap\Provider\Homebrew());
$config->addPhpProvider(new \AKlump\PhpSwap\Provider\Mamp());
```

The first provider added has the highest priority when resolving PHP versions.

## Terms

- **swapped**: PhpSwap is currently overriding the default PHP in this shell session.
- **saved**: A `.phpswap` swap file exists in the current directory or a parent directory.
- **swap file**: The `.phpswap` file that stores a project’s saved PHP version.
- **default PHP**: The PHP that would be used if PhpSwap were not active.
- **provider**: A source of PHP binaries, such as Homebrew or MAMP.

## PATH Management

PhpSwap tracks the PHP binary path it adds to `$PATH` using `PHPSWAP_ACTIVE_PATH`. Before each swap, it removes that previous active path from the current `$PATH`, updates `PHPSWAP_ORIGINAL_PATH`, and then prepends the newly selected PHP binary path. This prevents duplicate PhpSwap entries while preserving unrelated `$PATH` changes made by other tools during the shell session.

## Auto Swap on Directory Change

You may configure PhpSwap to automatically swap PHP when you change directories. If that directory has been setup with a swap file, PhpSwap will read that version and automatically swap. This saves you from having to manually type `phpswap`.

**Note: autoswap only works when changing to a directory that contains _.phpswap_**. That is, child directories will not autoswap, whereas **manually typing `phpswap` in a child directory will swap PHP based on a parent directory's configuration**.

### Setup in ZShell

Add the following to _.zshrc_, adjusting the path to phpswap.sh as appropriate.

```shell
# PhpSwap functionality to auto-swap PHP when cd-ing into a project.
# @url https://github.com/aklump/phpswap
function phpswap_autoswap {
  [[ -f ".phpswap" ]] && source ~/Code/Packages/cli/phpswap/app/phpswap.sh
}
autoload -Uz add-zsh-hook
add-zsh-hook chpwd phpswap_autoswap
```

### Setup in Bash

Add the following to _.bashrc_ or _.bash_profile_, adjusting the path to phpswap.sh as appropriate.

```shell
# PhpSwap functionality to auto-swap PHP when cd-ing into a project.
# @url https://github.com/aklump/phpswap
function cd {
  builtin cd "$@" || return
  [[ -f ".phpswap" ]] && source ~/Code/Packages/cli/phpswap/app/phpswap.sh
}
```
