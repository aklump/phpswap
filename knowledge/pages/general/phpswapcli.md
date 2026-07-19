<!--
id: phpswapcli
tags: ''
-->

# PhpSwap CLI

This package includes a CLI tool that lets you swap the CLI PHP being used in your terminal. The swap affects the current window only, by modifying and exporting the `$PATH`. Open a new window and the system default PHP will be at play.

## Installing `phpswap` CLI

Add an alias, which sources the `phpswap` command, adjusting the path as appropriate.

```shell
alias phpswap="source ~/Code/Packages/cli/phpswap/app/cli/bin/phpswap"
```

## Usage

For help type `phpswap -h`

### Register a Project's PHP Version (Permanent)

1. In your terminal, `cd` to your project's root directory.
2. Type `phpswap --save` or `phpswap 8.4 --save`
3. If prompted, select the version for your project (current plus all child directories).
5. Test it with `php -v`

This will create a `.phpswap` file in the current directory.

### Temporary Session Swap

By default, `phpswap` only affects the current shell session and does not create a `.phpswap` file.

```shell
phpswap
```

You can also specify the version:

```shell
phpswap 8.1
```

### Change a Project's PHP Version

To change the version for a configured project, just run `phpswap --save` again.

### Delete a Project's Configuration

To delete the `.phpswap` file in the current directory, use the `--delete` flag:

```shell
phpswap --delete
```

### Swap the Version

Once a project's version has been registered, you may swap the active PHP using `phpswap` from inside that project. Alternately, you can setup up _Auto Swap_ (see below).

```shell
cd my/great/project
phpswap
```

## PATH Management

PhpSwap tracks the PHP binary path it adds to `$PATH` using `PHPSWAP_ACTIVE_PATH`. Before each swap, it removes that previous active path from the current `$PATH`, updates `PHPSWAP_ORIGINAL_PATH`, and then prepends the newly selected PHP binary path. This prevents duplicate PhpSwap entries while preserving unrelated `$PATH` changes made by other tools during the shell session.

### Resetting the PATH

If you want to return to your non-PhpSwap path, you can run:

```shell script
phpswap -r
```

or the legacy command:

```shell script
phpswap reset
```

Or manually:

```shell script
export PATH="$PHPSWAP_ORIGINAL_PATH"
unset PHPSWAP_ORIGINAL_PATH
unset PHPSWAP_ACTIVE_PATH
```

## Auto Swap on Directory Change

You may configure PhpSwap to automatically swap PHP when you change directories. If that directory <s>or one of it's child directories</s>, has been setup with a PhpSwap version, PhpSwap will read that version and automatically swap. This saves you from having to manually type `phpswap`.

**Note: autoswap only works when changing to a directory that contains _.phpswap_**. That is, child directories will not autoswap, whereas **manually typing `phpswap` in a child directory will swap PHP based on a parent directory's configuration**.

### Setup in ZShell

Add the following to _.zshrc_, adjusting the path to phpswap as appropriate.

```shell
# PhpSwap functionality to auto-swap PHP when cd-ing into a project.
# @url https://github.com/aklump/phpswap
function phpswap_autoswap {
  [[ -f ".phpswap" ]] && source ~/Code/Packages/cli/phpswap/app/cli/bin/phpswap
}
autoload -Uz add-zsh-hook
add-zsh-hook chpwd phpswap_autoswap
```
