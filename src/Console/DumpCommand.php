<?php

declare(strict_types=1);

namespace Spacetab\Server\Console;

use Amp\Loop;
use Spacetab\Server\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DumpCommand
 *
 * @codeCoverageIgnore
 * @package Spacetab\Server\Console
 */
class DumpCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('dump')
            ->setDescription('Dump configuration')
            ->setHelp('Example of usage: `template dump`');
    }

    /**
     * Execute command, captain.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //@phpstan-ignore-next-line
        Loop::run(fn() => Application::fromGlobals()->dump());

        return 0;
    }
}
