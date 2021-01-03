<?php

declare(strict_types=1);

namespace Spacetab\Server\Console;

use Amp\Loop;
use Spacetab\Server\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateCommand
 *
 * @codeCoverageIgnore
 * @package Spacetab\Server\Console
 */
class GenerateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('generate')
            ->setDescription('Generate config for web-server')
            ->setHelp('Example of usage: `template generate`. If u want to change starting host or port, please change the __server_*.yaml configuration files.');
    }

    /**
     * Execute command, captain.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Throwable
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Loop::run(fn() => Application::fromGlobals()->generate());

        return 0;
    }
}
