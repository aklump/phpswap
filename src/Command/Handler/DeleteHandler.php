<?php

namespace AKlump\PhpSwap\Command\Handler;

use AKlump\PhpSwap\Helper\DeletePhpSwapFile;
use AKlump\PhpSwap\Helper\GetPhpSwapFilePath;
use AKlump\PhpSwap\Shell\ShellAction;
use AKlump\PhpSwap\Shell\ShellActionList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteHandler
{
    public function handle(InputInterface $input, OutputInterface $output, ShellActionList $actions)
    {
        $getPhpSwapFilePath = new GetPhpSwapFilePath();
        $swapfile = $getPhpSwapFilePath();

        if (!$swapfile) {
            $actions->add(ShellAction::message('No .phpswap file found to delete.', 'stderr'));
            return;
        }

        $deletePhpSwapFile = new DeletePhpSwapFile();
        if ($deletePhpSwapFile($swapfile)) {
            $actions->add(ShellAction::message(sprintf('Deleted swap file: %s', $swapfile)));
        } else {
            $actions->add(ShellAction::message(sprintf('Failed to delete %s', $swapfile), 'stderr'));
        }

        // Always attempt to unset the session per requirement 4.
        $actions->add(ShellAction::restoreOriginalPath());
        $actions->add(ShellAction::unsetEnv('PHPSWAP_ORIGINAL_PATH'));
        $actions->add(ShellAction::unsetEnv('PHPSWAP_ACTIVE_PATH'));
        $actions->add(ShellAction::unsetEnv('PHPSWAP'));
        $actions->add(ShellAction::message('PhpSwap unset. Restored default PHP for this shell session.'));
    }
}
