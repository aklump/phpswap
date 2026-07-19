<?php

namespace AKlump\PhpSwap\Helper;
use AKlump\PhpSwap\Provider\ProviderInterface;

class WritePhpSwapFile
{
    /**
     * @param string $path Full path to the .phpswap file to be written.
     * @param string $version The PHP version string.
     * @param ProviderInterface $provider The provider for the PHP version.
     * @param array $others Other binary paths to remove from PATH.
     *
     * @return bool
     */
    public function __invoke($path, $version, ProviderInterface $provider, array $others = array())
    {
        $content = array();
        $content[] = '#!/bin/bash';
        $content[] = sprintf('#[Config(php: %s)]', $version);
        $export = new GetExportPathCommand();
        $content[] = $export($provider, $version, $others);
        $content[] = 'echo "😎 PhpSwap(ped)!"';
        $content[] = "echo \"👉 $(php -v | grep -e 'PHP [0-9]\.[0-9]\.[0-9]' | head -n 1 | head -n 1)\"";
        $content[] = '';

        $result = file_put_contents($path, implode("\n", $content));
        if ($result !== false) {
            chmod($path, 0755);
            return true;
        }

        return false;
    }
}
