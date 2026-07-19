<?php

namespace AKlump\PhpSwap\Command;

use AKlump\PhpSwap\ConfigContainer;
use AKlump\PhpSwap\Shell\ShellActionBashRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplyCommand extends Command
{
    protected static $defaultName = '_apply';

    protected $config;

    public function __construct(ConfigContainer $config) {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setDescription('Internal command to convert JSON actions to Bash.')
            ->setHidden(true)
            ->addArgument('json', InputArgument::REQUIRED, 'The JSON payload.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $json = $input->getArgument('json');
        $data = json_decode($json, true);

        if (!$data || !isset($data['phpswap']) || $data['phpswap'] !== true) {
            return 1;
        }

        $renderer = new ShellActionBashRenderer();
        $output->write($renderer->render($data['actions']));

        return 0;
    }
}
