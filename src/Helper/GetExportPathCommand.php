<?php

namespace AKlump\PhpSwap\Helper;

use \UnexpectedValueException;

class GetExportPathCommand {

  /**
   * @param $provider
   * @param $version
   *
   * @return string
   */
  public function __invoke($provider, $version) {
    $binary = $provider->getBinary($version);
    if (!$binary) {
      throw new UnexpectedValueException("Could not find binary for version $version");
    }

    // Normalize binary path (remove double slashes)
    $binary = preg_replace('#//+#', '/', $binary);

    return <<<EOT
if [[ -n "\$PHPSWAP_ACTIVE_PATH" ]]; then
  _phpswap_original_path=""
  _phpswap_old_ifs="\$IFS"
  IFS=":"
  for _phpswap_entry in \$PATH; do
    if [[ "\$_phpswap_entry" != "\$PHPSWAP_ACTIVE_PATH" ]]; then
      if [[ -z "\$_phpswap_original_path" ]]; then
        _phpswap_original_path="\$_phpswap_entry"
      else
        _phpswap_original_path="\$_phpswap_original_path:\$_phpswap_entry"
      fi
    fi
  done
  IFS="\$_phpswap_old_ifs"
  export PHPSWAP_ORIGINAL_PATH="\$_phpswap_original_path"
elif [[ -z "\$PHPSWAP_ORIGINAL_PATH" ]]; then
  export PHPSWAP_ORIGINAL_PATH="\$PATH"
fi

export PHPSWAP_ACTIVE_PATH="$binary"
export PATH="\$PHPSWAP_ACTIVE_PATH:\$PHPSWAP_ORIGINAL_PATH"
unset _phpswap_original_path _phpswap_old_ifs _phpswap_entry
EOT;
  }
}
