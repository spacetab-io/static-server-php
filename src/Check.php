<?php

declare(strict_types=1);

namespace Spacetab\Server;

use Amp\File;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;
use Amp\Process\Process;
use Amp\ByteStream;
use Amp\Promise;
use Amp\Success;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spacetab\Configuration\ConfigurationInterface;
use Spacetab\Server\Exception\CheckException;
use Throwable;
use function Amp\call;

final class Check
{
    private ConfigurationInterface $conf;
    private NullLogger|LoggerInterface $logger;

    /**
     * Check constructor.
     *
     * @param \Spacetab\Configuration\ConfigurationInterface $conf
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(ConfigurationInterface $conf, ?LoggerInterface $logger = null)
    {
        $this->conf = $conf;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return \Amp\Promise<null>
     */
    public function ensuresRootIsExistsAndIndexFound(): Promise
    {
        $root = $this->conf->get('server.root');
        $index = rtrim($root, '/') . '/' .$this->conf->get('server.index');

        return call(function () use ($root, $index) {
            $check = yield [
                File\filesystem()->exists($root),
                File\filesystem()->isDirectory($root),
                File\filesystem()->exists($index),
            ];

            // Funny check.
            if (array_filter($check, fn($x) => $x === true) === []) {
                throw CheckException::missingRequiredFiles($root, $index);
            }

            $this->logger->info("Root path is exist and index file found.");
        });
    }

    /**
     * @return \Amp\Promise<null>
     * @throws \Spacetab\Server\Exception\CheckException
     */
    public function ensuresPrerenderCdnUrlIsPingable(): Promise
    {
        if ( ! $this->conf->get('server.prerender.enabled', false)) {
            $this->logger->info('Prerender is not enabled, skip check.');
            return new Success();
        }

        $url = $this->conf->get('server.prerender.cdnUrl', false);

        if (!$url) {
            throw CheckException::prerenderUrlIsNotSet();
        }

        $parsed = parse_url($url);

        if (!$parsed || (!isset($parsed['scheme']) || !isset($parsed['host']))) {
            throw CheckException::prerenderUrlIsInvalid();
        }

        $url = $parsed['scheme'] . '://' . $parsed['host'];

        $this->logger->info('Prerender URL must be pass http-check...');

        return call(function () use ($url) {
            try {
                $client = HttpClientBuilder::buildDefault();

                /** @var \Amp\Http\Client\Response $response */
                $response = yield $client->request(new Request($url));
                $status = $response->getStatus();
            } catch (HttpException $error) {
                throw CheckException::httpRequestFailed(
                    $error->getMessage(), $status ?? 0, isset($response) ? yield $response->getBody()->buffer() : ''
                );
            } catch (Throwable $error) {
                throw CheckException::httpRequestFailed($error->getMessage());
            }

            if ($status >= 200 && $status <= 299) {
                $this->logger->info('Prerender URL check passed.');
            } else {
                $this->logger->warning('HTTP status code for Prerender URL is out of range 200..299.');
            }
        });
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function ensuresPlatformSupportAsyncIo(): bool
    {
        $this->logger->debug('Check if platform supports async io...');

        switch (PHP_OS_FAMILY) {
            case 'Linux':
            case 'BSD':
                $this->logger->info('Platform supports async io.');
                $supports = true;
                break;
            default:
                $this->logger->info('Platform does not supports async io.');
                $supports = false;
        }

        return $supports;
    }

    /**
     * @return \Amp\Promise<null>
     */
    public function ensuresNginxIsInstalled(): Promise
    {
        return call(function () {
            $this->logger->debug('Running console command `which nginx` to check nginx installation...');

            $process = new Process(['which', 'nginx'], null);
            yield $process->start();

            $stdout = yield ByteStream\buffer($process->getStdout());
            $stderr = yield ByteStream\buffer($process->getStderr());
            $code   = yield $process->join();

            if ($code !== 0) {
                throw CheckException::errorOccurredInProcess($code, $stdout, $stderr);
            }

            if (strlen($stdout) < 1 || !str_contains($stdout, 'nginx')) {
                throw CheckException::nginxPathMustBePresent();
            }

            $this->logger->info('NGINX installed.');
        });
    }

    /**
     * @return \Amp\Promise<null>
     */
    public function ensuresNginxConfigurationIsValid(): Promise
    {
        $file = $this->conf->get('server.handler.options.config');

        return call(function () use ($file) {
            $this->logger->debug("Running console command `nginx -c {$file} -t` to check generated config nginx...");

            $process = new Process(['nginx', '-c', $file, '-t'], null);
            yield $process->start();

            $stderr = yield ByteStream\buffer($process->getStderr());
            $code   = yield $process->join();

            if ($code !== 0) {
                throw CheckException::errorOccurredInProcess($code, stderr: $stderr);
            }

            $this->logger->info($stderr);
        });
    }

    /**
     * @return \Amp\Promise<bool>
     */
    public function ensuresNginxBrotliModuleIsInstalled(): Promise
    {
        return call(function () {
            $this->logger->debug('Running console command `nginx -V` to check nginx installation...');

            # nginx -V send outputs to STDERR. https://trac.nginx.org/nginx/ticket/592
            $process = new Process(['nginx', '-V'], null);
            yield $process->start();

            $stderr = yield ByteStream\buffer($process->getStderr());
            $code   = yield $process->join();

            if ($code !== 0) {
                throw CheckException::errorOccurredInProcess(code: $code, stderr: $stderr);
            }

            if (str_contains($stderr, 'brotli')) {
                $this->logger->info('Nginx Brotli module installed. Turning it on.');
                return true;
            }

            $this->logger->info('Nginx Brotli module not installed. Turning off this compression method.');

            return false;
        });
    }
}
