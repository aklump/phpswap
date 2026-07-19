<?php

namespace AKlump\PhpSwap\Helper;

class DeletePhpSwapFile
{
    /**
     * @param string $path
     * @return bool
     */
    public function __invoke($path)
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
}
