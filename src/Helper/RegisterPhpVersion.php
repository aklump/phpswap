<?php

namespace AKlump\PhpSwap\Helper;

class RegisterPhpVersion {

  /**
   * @param string $swapfile
   * @param \AKlump\PhpSwap\Provider\ProviderInterface $provider
   * @param string $version
   *
   * @return void
   */
  public function __invoke($swapfile, $provider, $version) {
    $code = [];
    $code[] = '#!/bin/bash';
    $code[] = "#[Config(php: $version)]";
    $export = new GetExportPathCommand();
    $code[] = $export($provider, $version);
    $code[] = 'echo "😎 PhpSwap(ped)!"';
    $code[] = 'echo "👉 $(php -v | grep -e "PHP \d\.\d\.\d")"';

    file_put_contents($swapfile, implode(PHP_EOL, $code) . PHP_EOL);
    chmod($swapfile, 0755);
  }
}
