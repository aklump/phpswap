<?php

namespace AKlump\PhpSwap\Helper;

/**
 * Checks if a version matches a requested partial or full version.
 */
class VersionMatches {

  /**
   * @param string $available The full available version (e.g., '8.3.24').
   * @param string $requested The requested partial or full version (e.g., '8.3').
   *
   * @return bool
   */
  public function __invoke($available, $requested) {
    if ($available === $requested) {
      return TRUE;
    }
    $requested_parts = explode('.', $requested);
    $available_parts = explode('.', $available);

    foreach ($requested_parts as $i => $part) {
      if (!isset($available_parts[$i]) || $available_parts[$i] !== $part) {
        return FALSE;
      }
    }

    return TRUE;
  }
}
