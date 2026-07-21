## Roadblocks

## Critical

## Normal

- `[[ "$PHPSWAP" == "$swapfile" ]] && return` this should check the php version, not the swapfile; we only need to swap if version is different.
- handle if the patch version changes due to mamp upgrade
- wrap in a asdf plugin? https://asdf-vm.com/plugins/create.html; https://github.com/asdf-community/asdf-php

## Complete

- multi testing below 7.3 does not work due to composer 2.0; need a way to handle earlier versions?
