#!/usr/bin/env bash

# @file
#
# Controller for PhpSwap
#

x(){ echo "No script dir" >&2;return 1 2>/dev/null||exit 1;};if [ -n "${BASH_VERSION:-}" ];then s="${BASH_SOURCE[0]}";elif [ -n "${ZSH_VERSION:-}" ];then eval 's="${(%):-%x}"';else x;fi;[ -n "$s" ]||x;while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")"&&pwd)"||x;s="$(readlink "$s")"||x;[[ $s != /* ]]&&s="$d/$s";done;__DIR__="$(cd -P "$(dirname "$s")"&&pwd)"||x;if ! (return 0 2>/dev/null);then n="$(basename "$s")";echo "Error: $n must be sourced, not executed." >&2;echo "Use: source /path/to/$n" >&2;echo "Or create an alias like:" >&2;echo "  alias COMMAND_NAME='source /path/to/$n'" >&2;exit 1;fi;unset s d n;unset -f x

# ========= Config =========
PHP_CONTROLLER="$__DIR__/src/_phpswap.php"
export PHPSWAP_SH="$__DIR__/phpswap.sh"

# ========= Runtime PHP =========
PHPSWAP_RUNTIME_FILE="$__DIR__/.phpswap-runtime"

if [[ ! -f "$PHPSWAP_RUNTIME_FILE" ]]; then
  echo "❌ PhpSwap runtime PHP is not configured." >&2
  echo >&2
  echo "To repair this global PhpSwap installation:" >&2
  echo "  cd \"$__DIR__\" && ./phpswap-repair.sh" >&2
  echo >&2
  echo "Then source PhpSwap again." >&2
  return 1
fi

source "$PHPSWAP_RUNTIME_FILE"
PHPSWAP_RUNTIME_PHP="$(command -v php)"

if [[ -z "$PHPSWAP_RUNTIME_PHP" ]] || [[ ! -x "$PHPSWAP_RUNTIME_PHP" ]]; then
  echo "❌ PhpSwap runtime PHP could not be resolved from $PHPSWAP_RUNTIME_FILE." >&2
  echo "To repair this global PhpSwap installation:" >&2
  echo "  cd \"$__DIR__\" && ./phpswap-repair.sh" >&2
  return 1
fi

if ! "$PHPSWAP_RUNTIME_PHP" -v >/dev/null 2>&1; then
  echo "❌ PhpSwap runtime PHP is not executable: $PHPSWAP_RUNTIME_PHP" >&2
  echo "To repair this global PhpSwap installation:" >&2
  echo "  cd \"$__DIR__\" && ./phpswap-repair.sh" >&2
  return 1
fi

# ========= Execute PHP Controller =========
output=$("$PHPSWAP_RUNTIME_PHP" -d display_errors=0 -d display_startup_errors=0 "$PHP_CONTROLLER" "$@")
result=$?

if [[ $result -ne 0 ]]; then
  echo "$output" >&2
  return $result
fi

# ========= Apply Shell Actions or Print Output =========
if [[ "$output" == *'"phpswap":true'* ]] || [[ "$output" == *'"phpswap": true'* ]]; then
  bash_output=$("$PHPSWAP_RUNTIME_PHP" "$PHP_CONTROLLER" _apply "$output")
  eval "$bash_output"
else
  echo "$output"
fi

return 0
