<?php

declare(strict_types=1);

namespace Spacetab\Server\Handler;

use Amp\Promise;
use Amp\File;
use League\Plates\Engine;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spacetab\Configuration\ConfigurationInterface;
use Spacetab\Server\Check;
use Spacetab\Server\Header\ConvertsHeader;
use function Amp\call;

final class NginxHandler implements HandlerInterface
{
    private Engine $templates;
    private ?LoggerInterface $logger;
    private ConfigurationInterface $conf;
    private ConvertsHeader $convertHeaders;
    private Check $checker;

    /**
     * NginxHandler constructor.
     *
     * @param \Spacetab\Configuration\ConfigurationInterface $conf
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(ConfigurationInterface $conf, ?LoggerInterface $logger = null)
    {
        $this->templates      = new Engine(__DIR__ . DIRECTORY_SEPARATOR . 'templates');
        $this->convertHeaders = new ConvertsHeader($conf);
        $this->checker        = new Check($conf, $logger);
        $this->logger         = $logger ?: new NullLogger();
        $this->conf           = $conf;
    }

    public function handle(): Promise
    {
        $this->logger->info('Initialize the NGINX template generator...');

        $pidPath = $this->conf->get('server.handler.options.pid');
        $configPath = $this->conf->get('server.handler.options.config');

        $pidDir = pathinfo($pidPath, PATHINFO_DIRNAME);
        $configDir = pathinfo($configPath, PATHINFO_DIRNAME);

        return call(function () use ($pidPath, $configPath, $pidDir, $configDir) {
            $moduleBrotliInstalled = yield $this->checker->ensuresNginxBrotliModuleIsInstalled();
            $supportsAsyncIo = $this->checker->ensuresPlatformSupportAsyncIo();

            yield [
                $this->checker->ensuresRootIsExistsAndIndexFound(),
                $this->checker->ensuresPrerenderCdnUrlIsPingable(),
                $this->checker->ensuresNginxIsInstalled(),
            ];

            $this->logger->debug('Make recursively directories for pid and config file.', compact('pidDir', 'configDir'));

            yield [
                File\filesystem()->createDirectoryRecursively($pidDir),
                File\filesystem()->createDirectoryRecursively($configDir),
            ];

            $this->logger->debug("Touch pid file: {$pidPath}");

            yield File\filesystem()->touch($pidPath);

            $render = $this->templates->render(
                'nginx_default.conf',
                [
                    'serverRoot'              => $this->conf->get('server.root'),
                    'serverIndex'             => $this->conf->get('server.index'),
                    'serverPort'              => $this->conf->get('server.port'),
                    'serverHost'              => $this->conf->get('server.host'),
                    'prerenderEnabled'        => $this->conf->get('server.prerender.enabled'),
                    'prerenderCacheTTL'       => $this->conf->get('server.prerender.cacheTtl'),
                    'prerenderQueryParams'    => $this->getSortedQueryParams(),
                    'CDNUrl'                  => rtrim((string) $this->conf->get('server.prerender.cdnUrl'), '/'),
                    'CDNPath'                 => rtrim((string) $this->conf->get('server.prerender.cdnPath'), '/'),
                    'CDNFilePostfix'          => $this->conf->get('server.prerender.cdnFilePostfix'),
                    'prerenderHeaders'        => $this->conf->get('server.prerender.headers', []),
                    'prerenderResolver'       => $this->conf->get('server.prerender.resolver', false),
                    'headers'                 => $this->convertHeaders->convert(),
                    'connProcMethod'          => $this->getConnectionProcessingMethod(),
                    'pidLocation'             => $pidPath,
                    'moduleBrotliInstalled'   => $moduleBrotliInstalled,
                    'platformSupportsAsyncIo' => $supportsAsyncIo,
                    'configurationAsJson'     => $this->getConfigurationAsJson(),
                ]
            );

            $this->logger->debug("Write NGINX configuration to `{$configDir}`");

            yield File\filesystem()->write($configPath, $render);
            yield $this->checker->ensuresNginxConfigurationIsValid();
        });
    }

    private function getConfigurationAsJson(): string
    {
        $config = $this->conf->all();
        unset($config['server']);

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    private function getSortedQueryParams(): array
    {
        $array = $this->conf->get('server.prerender.queryParams', []);
        sort($array);

        return $array;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    private function getConnectionProcessingMethod(): string
    {
        switch (PHP_OS_FAMILY) {
            case 'Linux':
                return 'epoll';
            case 'Darwin':
            case 'BSD':
                return 'kqueue';
            default:
                return 'poll';
        }
    }
}
