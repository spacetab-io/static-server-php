<?php declare(strict_types=1);

namespace StaticServer\Tests;

use StaticServer\Server;
use Throwable;

class ApplicationTest extends TestCase
{
    public function testInit()
    {
        $path = __DIR__ . '/../tests/configuration';

        try {
            putenv('STAGE=tests');
            putenv("CONFIG_PATH=$path");
            Server::silent()->dryRun();
        } catch (Throwable $e) {
            printf($e);
            $this->assertFalse((bool) $e);
        }

        // workaround for simple check of application startup.
        $this->assertTrue(true);
    }

    public function testVcsSha1()
    {
        $this->setOutputCallback(function () {
            $path = __DIR__ . '/../tests/configuration';

            try {
                putenv('STAGE=tests');
                putenv("CONFIG_PATH=$path");
                putenv("VCS_SHA1=test");
                Server::new()->dryRun();
            } catch (Throwable $e) {
                printf($e);
                $this->assertFalse((bool) $e);
            }

            // workaround for simple check of application startup.
            $this->assertTrue(true);
        });
    }
}