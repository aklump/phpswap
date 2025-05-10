## Roadblocks

## Critical

## Normal

- `[[ "$PHPSWAP" == "$swapfile" ]] && return` this should check the php version, not the swapfile; we only need to swap if version is different.
- Update `->setVersion('0.0.0')` on build.
- handle if the patch version changes due to mamp upgrade
- prevent nested .phpswap/foo/.phpswap ? Seems like that could be problematic.
- update docs based on recent changes
- cleanup, may have code no longer needed, in cli php
- wrap in a asdf plugin? https://asdf-vm.com/plugins/create.html; https://github.com/asdf-community/asdf-php

### Bash Autoswap

- Bash autoswap support?

```shell  
 
#BASH  
# Create a function that wraps the original cd command
cd() {
    builtin cd "$@"  # Call the original cd command

    # Execute auto.sh only if cd was successful
    if [[ $? -eq 0 && -x "$PWD/auto.sh" ]]; then
      "$PWD/auto.sh"
    fi
}
```

## Complete
