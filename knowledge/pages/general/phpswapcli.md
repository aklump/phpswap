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

### Register a Project's PHP Version

1. In your terminal, `cd` to your project's root directory.
2. Type `phpswap`
1. Select the version for your project (current plus all child directories).
4. Test it with `php -v`

### Register a Different Version

To change the version for a configured project, use `phpswap set` to access the

### Swap the Version

Once a project's version has been registered, you may swap the active PHP using `phpswap` from inside that project. Alternately, you can setup up _Auto Swap_ (see below).

```shell
cd my/great/project
phpswap
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
