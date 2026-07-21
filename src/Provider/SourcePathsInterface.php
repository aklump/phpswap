<?php

namespace AKlump\PhpSwap\Provider;

/**
 * Implemented by providers that can declare which filesystem paths, when
 * modified, indicate their set of available PHP versions may have changed.
 *
 * ProviderService uses these paths to build a cheap cache-invalidation
 * fingerprint (a handful of stat() calls) instead of re-running the expensive
 * version discovery on every invocation.
 *
 * This is intentionally a separate, optional capability interface rather than a
 * method on ProviderInterface so that custom providers remain compatible; a
 * provider that does not implement it simply does not contribute to the cache
 * fingerprint.
 */
interface SourcePathsInterface {

  /**
   * Get the paths whose modification time signals available versions changed.
   *
   * @return string[]
   *   Absolute filesystem paths (directories) to watch for changes. May be
   *   empty when none of the provider's sources are present on this system.
   */
  public function getSourcePaths();

}
