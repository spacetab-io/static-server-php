<?php

declare(strict_types=1);

namespace Spacetab\Server;

use Amp\Promise;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spacetab\Configuration\Configuration;
use Spacetab\Logger\Logger;
use Spacetab\Server\Handler\HandlerFactory;
use Spacetab\Server\Handler\HandlerInterface;
use function Amp\call;

final class Application
{
    private const SERVER_LOG_CHANNEL = 'Server';
    private const DEFAULT_STAGE = 'defaults';

    private Configuration $conf;
    private HandlerInterface $handler;
    private LoggerInterface $logger;
    private Inject          $injector;

    /**
     * Application constructor.
     *
     * @param string $stage
     * @param string $sha1
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\HandlerException
     */
    public function __construct(
        private string $stage = '',
        private string $sha1 = ''
    )
    {
        $conf = Configuration::auto($stage);
        $conf->load();

        $logger = match ($conf->get('server.logger.enabled')) {
            true => Logger::default(self::SERVER_LOG_CHANNEL, $conf->get('server.logger.level')),
            default => new NullLogger()
        };

        $this->conf     = $conf;
        $this->logger   = $logger;
        $this->injector = new Inject($conf, $logger);

        $factory = new HandlerFactory($conf, $logger);
        $this->handler = $factory->createHandler(
            $this->conf->get('server.handler.name')
        );
    }

    /**
     * @return self
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\HandlerException
     */
    public static function fromGlobals(): self
    {
        $stage = getenv('STAGE') ?: self::DEFAULT_STAGE;
        $sha1  = getenv('VCS_SHA1') ?: '';

        return new Application($stage, $sha1);
    }

    /**
     * @return Promise<null>
     */
    public function generate(): Promise
    {
        $format = 'State: STAGE=%s SHA1=%s VERSION=%s CONFIG_PATH=%s';
        $message = sprintf($format, $this->stage, $this->sha1, getenv('SERVER_VERSION'), $this->conf->getPath());
        $this->logger->info($message);

        return call(function () {
            yield [
                $this->injector->injectSecurityTxtFile(),
                $this->injector->injectConfigJsFile($this->stage, $this->sha1),
                $this->injector->injectConfigJsScriptToIndexFile()
            ];

            yield $this->handler->handle();
        });
    }

    /**
     * For debug only.
     *
     * @return void
     */
    public function dump(): void
    {
        printf("CONFIG_PATH = %s\n", $this->conf->getPath());
        printf("STAGE = %s\n", $this->conf->getStage());

        printf("%s\n\n", $this->conf->dump());
    }

    public function getHandlerPidPath(): string
    {
        return $this->conf->get('server.handler.options.pid');
    }

    public function getHandlerConfigPath(): string
    {
        return $this->conf->get('server.handler.options.config');
    }
}
