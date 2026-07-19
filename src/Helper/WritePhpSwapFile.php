<?php

namespace AKlump\PhpSwap\Helper;

class WritePhpSwapFile
{
    /**
     * @param string $path Full path to the .phpswap file to be written.
     * @param string $version The PHP version string.
     * @param string $bin_path The path to the PHP bin directory.
     *
     * @return bool
     */
    public function __invoke($path, $version, $bin_path)
    {
        $content = array();
        $content[] = '#!/bin/bash';
        $content[] = sprintf('#[Config(php: %s)]', $version);
        $content[] = sprintf('export PATH="%s:$PATH"', $bin_path);
        $content[] = 'echo "😎 PhpSwap(ped)!"';
        $content[] = "echo \"👉 $(php -v | grep -e 'PHP [0-9]\.[0-9]\.[0-9]' | head -n 1)\"";
        $content[] = '';

        $result = file_put_contents($path, implode("\n", $content));
        if ($result !== false) {
            chmod($path, 0755);
            return true;
        }

        return false;
    }
}
