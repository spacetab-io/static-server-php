<?php

declare(strict_types=1);

namespace Spacetab\Tests\Server;

use Generator;
use Spacetab\Configuration\Configuration;
use Spacetab\Server\Application;

class ApplicationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $path = __DIR__ . '/../tests/configuration';
        putenv('STAGE=tests');
        putenv("CONFIG_PATH=$path");
        putenv("VCS_SHA1=test");
    }

    public function testDump()
    {
        Application::fromGlobals()->dump();
        $this->expectOutputRegex('/CONFIG_PATH.*/');
    }

    public function testGetHandlerPidPath()
    {
        $conf = new Configuration(__DIR__ . '/../tests/configuration', 'tests');
        $conf->load();

        $this->assertSame($conf->get('server.handler.options.pid'), Application::fromGlobals()->getHandlerPidPath());
    }

    public function testGetHandlerConfigPath()
    {
        $conf = new Configuration(__DIR__ . '/../tests/configuration', 'tests');
        $conf->load();

        $this->assertSame($conf->get('server.handler.options.config'), Application::fromGlobals()->getHandlerConfigPath());
    }

    public function testGenerate(): Generator
    {
        $this->assertNull(yield Application::fromGlobals()->generate());
    }
}
