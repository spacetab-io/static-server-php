<?php

declare(strict_types=1);

namespace Spacetab\Tests\Server\Handler;

use Spacetab\Configuration\Configuration;
use Spacetab\Server\Exception\HandlerException;
use Spacetab\Server\Handler\HandlerFactory;
use Spacetab\Server\Handler\NginxHandler;
use Spacetab\Tests\Server\TestCase;

class HandlerFactoryTest extends TestCase
{

    public function testCreateHandler()
    {
        $conf = new Configuration(__DIR__ . '/../configuration', 'tests');
        $conf->load();

        $factory = new HandlerFactory($conf);

        $this->assertInstanceOf(NginxHandler::class, $factory->createHandler('nginx'));

        $this->expectException(HandlerException::class);
        $this->expectExceptionMessageMatches('/`whoops` – handler not supported.*/');
        $factory->createHandler('whoops');
    }
}
