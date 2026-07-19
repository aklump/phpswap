<?php

namespace AKlump\PhpSwap\Helper;

class GetDefaultPhp
{
    /**
     * @return array|null Array with 'version' and 'path' or null if not found.
     */
    public function __invoke()
    {
        // 1. Check if we have an original path stored.
        $original_path = getenv('PHPSWAP_ORIGINAL_PATH');
        if ($original_path) {
            $paths = explode(PATH_SEPARATOR, $original_path);
            foreach ($paths as $path) {
                $php = $path . DIRECTORY_SEPARATOR . 'php';
                if (file_exists($php) && is_executable($php)) {
                    return array(
                        'version' => 'default/system',
                        'path' => $php,
                    );
                }
            }
        }

        // 2. Check common locations.
        $locations = array('/usr/bin/php', '/usr/local/bin/php', '/bin/php');
        foreach ($locations as $location) {
            if (file_exists($location) && is_executable($location)) {
                return array(
                    'version' => 'default/system',
                    'path' => $location,
                );
            }
        }

        // 3. Try to find it in the current PATH, but if we are swapped, we might pick up the swapped one.
        // We already checked PHPSWAP_ORIGINAL_PATH above.
        // If we are NOT swapped, which php should return the default one.
        if (!getenv('PHPSWAP_ACTIVE_PATH')) {
            $php = trim(shell_exec('which php'));
            if ($php && file_exists($php) && is_executable($php)) {
                return array(
                    'version' => 'default/system',
                    'path' => $php,
                );
            }
        }

        return null;
    }
}
