<!--
id: developers
tags: ''
-->

## Usage

- [ ] Create `alias phpswqp='source phpswap.sh cli'`

## Direct use in test contollers

- [ ] ./phpswap_execute.php using 8.4 'php -v' | run a command under a different PHP version

## CLI

- [ ] phpswap show | list available versions and paths (table: version | path)
- [ ] phpswap status | show phpswap env vars; show the path to the phpswap.sh file if found

- [ ] phpswap | set the php version for the session
    if .phpswap can be found in this or parent dir then exectue it and done
    else act as if `--set` was called

### Changing PHP using $PATH

- [ ] phpswap --set | select from all available versions and apply
- [ ] phpswap --unset | return to the default php version
  
### Persistence

- [ ] phpswap --save | set the php version for long term (.phpswap file in CWD); this will apply the same logic of finding the version from .phpswap or prompting if none found
- [ ] phpswap --delete | delete the phpswap persistent file in this or first parent directory

