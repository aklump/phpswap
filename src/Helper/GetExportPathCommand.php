<?php

namespace AKlump\PhpSwap\Helper;

use \UnexpectedValueException;

class GetExportPathCommand {

  /**
   * @param $provider
   * @param $version
   * @param array $others
   *
   * @return string
   */
  public function __invoke($provider, $version, array $others = array()) {
    $binary = $provider->getBinary($version);
    if (!$binary) {
      throw new UnexpectedValueException("Could not find binary for version $version");
    }

    // Normalize binary path (remove double slashes and trailing slashes)
    $binary = preg_replace('#//+#', '/', rtrim($binary, '/'));

    $others_str = '';
    foreach ($others as $other) {
      $normalized_other = preg_replace('#//+#', '/', rtrim($other, '/'));
      $others_str .= ':' . $normalized_other;
    }
    if ($others_str) {
      $others_str .= ':';
    }

    return <<<EOT
_phpswap_new_path=""
_phpswap_old_ifs="\$IFS"
_phpswap_others="$others_str"
IFS=":"
for _phpswap_entry in \$PATH; do
  if [[ "\$_phpswap_entry" != "\$PHPSWAP_ACTIVE_PATH" && "\$_phpswap_entry" != "$binary" && ( -z "\$_phpswap_others" || "\$_phpswap_others" != *":\$_phpswap_entry:"* ) ]]; then
    if [[ -z "\$_phpswap_new_path" ]]; then
      _phpswap_new_path="\$_phpswap_entry"
    else
      _phpswap_new_path="\$_phpswap_new_path:\$_phpswap_entry"
    fi
  fi
done
IFS="\$_phpswap_old_ifs"
export PATH="\$_phpswap_new_path"

if [[ -z "\$PHPSWAP_ORIGINAL_PATH" ]]; then
  export PHPSWAP_ORIGINAL_PATH="\$PATH"
fi

export PHPSWAP_ACTIVE_PATH="$binary"
export PATH="\$PHPSWAP_ACTIVE_PATH:\$PATH"
unset _phpswap_new_path _phpswap_old_ifs _phpswap_entry _phpswap_others
EOT;
  }
}
