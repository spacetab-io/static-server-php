<?php

declare(strict_types=1);

namespace Spacetab\Tests\Server;

use Generator;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Spacetab\Configuration\Configuration;
use Spacetab\Server\Check;
use Spacetab\Server\Exception\CheckException;
use Spacetab\Server\Handler\NginxHandler;

class CheckTest extends TestCase
{
    public function testEnsuresPlatformSupportAsyncIoSucceeded()
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        try {
            $checker = new Check($conf);

            $this->assertTrue($checker->ensuresPlatformSupportAsyncIo());
        } catch (\Throwable) {
            $this->markTestSkipped('Your platform does not support async i/o (nginx)? Oh...');
        }
    }

    public function testEnsuresPlatformSupportAsyncIoFailure()
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        try {
            $checker = new Check($conf);

            $this->assertFalse($checker->ensuresPlatformSupportAsyncIo());
        } catch (\Throwable) {
            $this->markTestSkipped('Your platform support async i/o (nginx)? Oh...');
        }
    }

    public function testEnsuresNginxBrotliModuleIsInstalledSucceeded(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        try {
            $checker = new Check($conf);

            $this->assertTrue(yield $checker->ensuresNginxBrotliModuleIsInstalled());
        } catch (\Throwable) {
            $this->markTestSkipped('NGINX Brotli module not installed? Oh...');
        }
    }

    public function testEnsuresNginxBrotliModuleIsInstalledFailure(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        try {
            $checker = new Check($conf);

            $this->assertFalse(yield $checker->ensuresNginxBrotliModuleIsInstalled());
        } catch (\Throwable) {
            $this->markTestSkipped('NGINX Brotli module installed? Oh...');
        }
    }

    /**
     * @return \Generator
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\CheckException
     */
    public function testEnsuresPrerenderCdnUrlIsPingableWhenPrerenderDisabled(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with($this->stringStartsWith('Prerender is not enabled'));

        $checker = new Check($conf, $logger);

        $this->assertNull(yield $checker->ensuresPrerenderCdnUrlIsPingable());
    }

    /**
     * @return \Generator
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\CheckException
     */
    public function testEnsuresPrerenderCdnUrlIsPingableSucceeded(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'nginx_conf');
        $conf->load();

        $checker = new Check($conf);
        $this->assertNull(yield $checker->ensuresPrerenderCdnUrlIsPingable());
    }

    /**
     * @return \Generator
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\CheckException
     */
    public function testEnsuresPrerenderCdnUrlIsNotPingable(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'not_pingable_prerender');
        $conf->load();

        $this->expectException(CheckException::class);
        $this->expectExceptionMessageMatches('/Error occurred in http request.*/');

        $checker = new Check($conf);
        $this->assertNull(yield $checker->ensuresPrerenderCdnUrlIsPingable());
    }

    /**
     * @return \Generator
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\CheckException
     */
    public function testEnsuresPrerenderCdnUrlIsInvalid(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'invalid_prerender_url');
        $conf->load();

        $this->expectException(CheckException::class);
        $this->expectExceptionMessageMatches('/Prerender CDN URL is invalid.*/');

        $checker = new Check($conf);
        $this->assertNull(yield $checker->ensuresPrerenderCdnUrlIsPingable());
    }

    /**
     * @return \Generator
     * @throws \Spacetab\Configuration\Exception\ConfigurationException
     * @throws \Spacetab\Server\Exception\CheckException
     */
    public function testEnsuresPrerenderCdnUrlIsNotSet(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'not_set_prerender_url');
        $conf->load();

        $this->expectException(CheckException::class);
        $this->expectExceptionMessageMatches('/Prerender CDN URL not set.*/');

        $checker = new Check($conf);
        $this->assertNull(yield $checker->ensuresPrerenderCdnUrlIsPingable());
    }

    public function testEnsuresNginxConfigurationIsValid(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'nginx_conf');
        $conf->load();

        $handler = new NginxHandler($conf);
        yield $handler->handle();

        $checker = new Check($conf);
        $this->assertNull(yield $checker->ensuresNginxConfigurationIsValid());
    }

    public function testEnsuresRootIsExistsAndIndexFoundSucceeded(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        $checker = new Check($conf);
        $this->assertNull(yield $checker->ensuresRootIsExistsAndIndexFound());
    }

    public function testEnsuresRootIsExistsAndIndexFoundFailure(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'not_found');
        $conf->load();

        $this->expectException(CheckException::class);
        $this->expectExceptionMessageMatches('/Root server directory.*/');

        $checker = new Check($conf);

        $this->assertNull(yield $checker->ensuresRootIsExistsAndIndexFound());
    }

    public function testEnsuresNginxIsInstalled(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        $checker = new Check($conf);

        $this->assertNull(yield $checker->ensuresNginxIsInstalled());
    }
}
