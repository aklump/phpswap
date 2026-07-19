#!/usr/bin/env bash

# @file
#
# Controller for PhpSwap
#

s="${BASH_SOURCE[0]}";[[ "$s" ]] || s="${(%):-%N}";while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")" && pwd)";s="$(readlink "$s")";[[ $s != /* ]] && s="$d/$s";done;__DIR__=$(cd -P "$(dirname "$s")" && pwd)

# ========= Start config =========
# The PHP version to use when running this script, which must match the version
# used to install the composer dependencies.
SELF_PHP=/Applications/MAMP/bin/php/php8.4.11/bin/php
PHP_CONTROLLER="$__DIR__/src/_phpswap.php"
# ========= End config =========

# ========= Require this file to be sourced  =========
# Works in bash, zsh, and most POSIX-like shells.
if ! (return 0 2>/dev/null); then
  if [ -n "${BASH_SOURCE:-}" ]; then
    _sourced_file="${BASH_SOURCE[0]}"
  elif [ -n "${ZSH_VERSION:-}" ]; then
    _sourced_file="${(%):-%N}"
  else
    _sourced_file="$0"
  fi
  _sourced_name="$(basename "$_sourced_file")"
  echo "Error: $_sourced_name must be sourced, not executed." >&2
  echo "Use: source /path/to/$_sourced_name" >&2
  echo "Or create an alias like:" >&2
  echo "  alias COMMAND_NAME='source /path/to/$_sourced_name'" >&2
  unset _sourced_file _sourced_name
  exit 1
fi

# ========= Use existing .phpswap to set the version =========
if [[ -z "$1" ]]; then
  swapfile=$(pwd)
  while [[ -n "$swapfile" && "$swapfile" != "/" ]]; do
    if [[ -f "$swapfile/.phpswap" ]]; then
      swapfile="$swapfile/.phpswap"
      # Once swapped in a project, don't do it again until the swapfile changes.
      [[ "$PHPSWAP" == "$swapfile" ]] && return
      # We have to use BASH to be able to alter the $PATH of the current shell
      source "$swapfile"
      export PHPSWAP="$swapfile"
      return
    fi
    swapfile=$(dirname "$swapfile")
  done
fi

if [[ " $* " == *" --delete "* ]]; then
  _phpswap_target=""
  _phpswap_dir=$(pwd)
  while [[ -n "$_phpswap_dir" && "$_phpswap_dir" != "/" ]]; do
    if [[ -f "$_phpswap_dir/.phpswap" ]]; then
      _phpswap_target="$_phpswap_dir/.phpswap"
      break
    fi
    _phpswap_dir=$(dirname "$_phpswap_dir")
  done

  if [[ -n "$_phpswap_target" ]]; then
    if [[ -n "$PHPSWAP_ORIGINAL_PATH" ]]; then
      export PATH="$PHPSWAP_ORIGINAL_PATH"
      unset PHPSWAP_ORIGINAL_PATH
      unset PHPSWAP_ACTIVE_PATH
      unset PHPSWAP
    fi
    rm "$_phpswap_target"
    echo "🗑  $_phpswap_target has been deleted." >&2
  else
    echo "No .phpswap file found to delete." >&2
  fi
  unset _phpswap_target _phpswap_dir
  return
fi

# ========= Pass on to the PHP controller for anything else =========
controller_args=("$@")
if [[ -z "${1:-}" ]]; then
  controller_args=("cli" "${controller_args[@]}")
fi
output=$("$SELF_PHP" -d display_errors=0 -d display_startup_errors=0 "$PHP_CONTROLLER" "${controller_args[@]}")

if [[ $? -eq 0 ]]; then
  if [[ "$output" == export* ]] || [[ "$output" == if* ]] || [[ "$output" == rm* ]]; then
     eval "$output"
  elif [[ "$*" == *"-h"* ]] || [[ "$*" == *"--help"* ]] || [[ "$*" == *"-V"* ]] || [[ "$*" == *"--version"* ]]; then
     echo "$output"
  elif [[ -f .phpswap ]]; then
     source .phpswap
  fi
else
  echo "$output"
fi
return
