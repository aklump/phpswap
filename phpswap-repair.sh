#!/usr/bin/env bash

# @file
# Repair the PhpSwap runtime PHP configuration.

set -e

x(){ echo "No script dir" >&2;return 1 2>/dev/null||exit 1;};if [ -n "${BASH_VERSION:-}" ];then s="${BASH_SOURCE[0]}";elif [ -n "${ZSH_VERSION:-}" ];then eval 's="${(%):-%x}"';else x;fi;[ -n "$s" ]||x;while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")"&&pwd)"||x;s="$(readlink "$s")"||x;[[ $s != /* ]]&&s="$d/$s";done;__DIR__="$(cd -P "$(dirname "$s")"&&pwd)"||x;unset s d;unset -f x

cd "$__DIR__"

PHP="$(command -v php || true)"
if [[ -z "$PHP" ]] || [[ ! -x "$PHP" ]]; then
  echo "❌ No executable php found in PATH." >&2
  echo "Put the PHP version you want PhpSwap to use first in PATH, then run:" >&2
  echo "  cd \"$__DIR__\"" >&2
  echo "  ./phpswap-repair.sh" >&2
  exit 1
fi

PHP_VERSION="$("$PHP" -r 'echo PHP_VERSION;' 2>/dev/null || true)"
if [[ -z "$PHP_VERSION" ]]; then
  echo "❌ Failed to determine PHP version from: $PHP" >&2
  exit 1
fi

PHP_BIN_DIR="$(dirname "$PHP")"
PHPSWAP_RUNTIME_FILE="$__DIR__/.phpswap-runtime"

cat > "$PHPSWAP_RUNTIME_FILE" <<EOF
#!/bin/bash
#[Config(php: $PHP_VERSION)]
export PATH="$PHP_BIN_DIR:\$PATH"
EOF

chmod 0755 "$PHPSWAP_RUNTIME_FILE"

echo "✅ Wrote $PHPSWAP_RUNTIME_FILE"
echo "👉 Runtime PHP: $PHP"
echo "👉 Runtime PHP version: $PHP_VERSION"

if ! command -v composer >/dev/null 2>&1; then
  echo "❌ composer was not found in PATH." >&2
  echo "Install Composer or put it in PATH, then run this repair script again." >&2
  exit 1
fi

echo "📦 Updating Composer dependencies using runtime PHP..."
composer update --no-interaction

echo "🧪 Verifying PhpSwap runtime..."
"$PHP" -d display_errors=0 -d display_startup_errors=0 "$__DIR__/src/_phpswap.php" --help >/dev/null

echo
echo
echo "✅ PhpSwap repair complete."
echo
echo "## Shell Alias"
echo
echo "‼️ Add the following to your shell profile, e.g. _.zshrc_:"
echo
cat <<EOF
\`\`\`shell
alias phpswap='source "$__DIR__/phpswap.sh"'
\`\`\`
EOF
echo
echo "After updating your shell profile, reload it:"
echo
cat <<EOF
\`\`\`shell
source ~/.zshrc
\`\`\`
EOF
echo
echo "## Auto Swap on Directory Change"
echo
echo "You may configure PhpSwap to automatically swap PHP when you change directories."
echo "If that directory has been setup with a swap file, PhpSwap will read that version"
echo "and automatically swap. This saves you from having to manually type \`phpswap\`."
echo
echo "**Note: autoswap only works when changing to a directory that contains _.phpswap_**."
echo "That is, child directories will not autoswap, whereas manually typing \`phpswap\`"
echo "in a child directory will swap PHP based on a parent directory's configuration."
echo
echo "### Setup in Bash"
echo
echo "‼️ Add the following to _.bashrc_ or _.bash_profile_:"
echo
cat <<EOF
\`\`\`shell
# PhpSwap functionality to auto-swap PHP when cd-ing into a project.
# @url https://github.com/aklump/phpswap
function cd {
  builtin cd "\$@" || return
  [[ -f ".phpswap" ]] && source "$__DIR__/phpswap.sh"
}
\`\`\`
EOF
echo
echo "### Setup in ZShell"
echo
echo "‼️ Add the following to _.zshrc_:"
echo
cat <<EOF
\`\`\`shell
# PhpSwap functionality to auto-swap PHP when cd-ing into a project.
# @url https://github.com/aklump/phpswap
function phpswap_autoswap {
  [[ -f ".phpswap" ]] && source "$__DIR__/phpswap.sh"
}
autoload -Uz add-zsh-hook
add-zsh-hook chpwd phpswap_autoswap
\`\`\`
EOF
echo
