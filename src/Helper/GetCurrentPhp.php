<?php

namespace AKlump\PhpSwap\Helper;

class GetCurrentPhp
{
    /**
     * Get the currently active PHP binary, version, and bin directory.
     *
     * @return array|null
     *   An array with 'version', 'binary', and 'bin_dir' keys, or NULL on failure.
     */
    public function __invoke()
    {
        $binary = trim(shell_exec('command -v php'));
        if (empty($binary) || !is_executable($binary)) {
            return null;
        }
        $version = trim(shell_exec(escapeshellarg($binary) . ' -r ' . escapeshellarg('echo PHP_VERSION;')));
        if (empty($version)) {
            return null;
        }

        return array(
            'version' => $version,
            'binary' => $binary,
            'bin_dir' => dirname($binary),
        );
    }
}
