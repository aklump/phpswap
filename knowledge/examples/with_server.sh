#!/usr/bin/env bash
s="${BASH_SOURCE[0]}";[[ "$s" ]] || s="${(%):-%N}";while [ -h "$s" ];do d="$(cd -P "$(dirname "$s")" && pwd)";s="$(readlink "$s")";[[ $s != /* ]] && s="$d/$s";done;__DIR__=$(cd -P "$(dirname "$s")" && pwd)

cd "$__DIR__/.."

! [ -e ./vendor/bin/phpswap ] && echo "You seem to be missing this: https://github.com/aklump/phpswap" && echo "Try running: composer require --dev aklump/phpswap" && exit 1

verbose=''
if [[ "${*}" == *'-v'* ]]; then
  verbose='-v'
fi

function start_server() {
  local version=$1

  PHP=$(./vendor/bin/phpswap use $version 'command -v php')
  # Use a subshell for the background process to suppress "Terminated" messages
  # when the process is eventually killed.
  { exec $PHP -S 127.0.0.1:8080 -t "$__DIR__/../web" &>/dev/null; } & disown
  while ! nc -z 127.0.0.1 8080 2>/dev/null; do
    sleep 0.1
  done
}

function stop_server() {
  (lsof -t -i:8080 | xargs kill 2>/dev/null) 2>/dev/null
  while nc -z 127.0.0.1 8080 2>/dev/null; do
    sleep 0.1
  done
}

function run_tests() {
  local version=$1

  stop_server
  start_server "$version"
  ./vendor/bin/phpswap use "$version" $verbose './vendor/bin/phpunit -c tests_phpunit'
  stop_server
  echo
}

run_tests 7.3
run_tests 7.4
run_tests 8.0
run_tests 8.1
run_tests 8.2
run_tests 8.3
run_tests 8.4
