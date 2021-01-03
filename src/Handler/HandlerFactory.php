<?php

declare(strict_types=1);

namespace Spacetab\Server\Handler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spacetab\Configuration\ConfigurationInterface;
use Spacetab\Server\Exception\HandlerException;

final class HandlerFactory
{
    private ConfigurationInterface $conf;
    private ?LoggerInterface $logger;

    /**
     * HandlerFactory constructor.
     *
     * @param \Spacetab\Configuration\ConfigurationInterface $conf
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(ConfigurationInterface $conf, ?LoggerInterface $logger = null)
    {
        $this->conf   = $conf;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param string $name
     * @return \Spacetab\Server\Handler\HandlerInterface
     * @throws \Spacetab\Server\Exception\HandlerException
     */
    public function createHandler(string $name): HandlerInterface
    {
        return match ($name) {
            HandlerInterface::HANDLER_NGINX => new NginxHandler($this->conf, $this->logger),
            default => throw HandlerException::handlerNotSupported($name)
        };
    }
}
