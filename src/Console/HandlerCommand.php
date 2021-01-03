<?php

declare(strict_types=1);

namespace Spacetab\Server\Console;

use Spacetab\Server\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HandlerCommand
 *
 * @codeCoverageIgnore
 * @package Spacetab\Server\Console
 */
class HandlerCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('handler')
            ->setDescription('Command to dump handler pid & config path\'s.');

        $this->addOption('dump', mode: InputOption::VALUE_OPTIONAL, description: 'Options: `pid` or `config`. ', default: 'pid');
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
        $code = 0;

        $string = match ($input->getOption('dump')) {
            'pid' => Application::fromGlobals()->getHandlerPidPath(),
            'config' => Application::fromGlobals()->getHandlerConfigPath(),
            default => $code = 1
        };

        $output->write($string);

        return $code;
    }
}
