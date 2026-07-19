<?php

namespace AKlump\PhpSwap\Helper;

class FindProviderVersionForCurrentPhp {

  /**
   * Match the current PHP to a provider-managed PHP version.
   *
   * @param array $current The current PHP info from GetCurrentPhp.
   * @param ProviderService $phpswap_providers The provider service.
   *
   * @return array|null
   *   An array with 'version', 'bin_dir' and 'provider' keys, or NULL on failure.
   */
  public function __invoke(array $current, ProviderService $phpswap_providers) {
    $versions = $phpswap_providers->listAll();
    $current_binary_realpath = realpath($current['binary']);

    $all_providers = array();
    foreach ($phpswap_providers->getProviders() as $v => $p) {
      $all_providers[$v] = $p;
    }

    // 1. Exact binary match or Realpath match
    foreach ($versions as $version) {
      try {
        $bin_dir = $phpswap_providers->getBinary($version);
        $provider_binary = $bin_dir . '/php';
        if ($provider_binary === $current['binary']) {
          return array(
            'version' => $version,
            'bin_dir' => $bin_dir,
            'provider' => $all_providers[$version],
          );
        }
        if ($current_binary_realpath && realpath($provider_binary) === $current_binary_realpath) {
          return array(
            'version' => $version,
            'bin_dir' => $bin_dir,
            'provider' => $all_providers[$version],
          );
        }
      }
      catch (\Exception $e) {
        continue;
      }
    }

    // 2. Exact version match
    foreach ($versions as $version) {
      if ($version === $current['version']) {
        try {
          $bin_dir = $phpswap_providers->getBinary($version);

          return array(
            'version' => $version,
            'bin_dir' => $bin_dir,
            'provider' => $all_providers[$version],
          );
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }

    return NULL;
  }
}
