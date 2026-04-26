<?php

declare(strict_types=1);

namespace Spacetab\Tests\Server\Handler;

use Generator;
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

    /**
     * Кастомный setup/rewrite (новый API 5.0.0) попадает в сгенерированный nginx config 1:1.
     * Рендерим шаблон напрямую через Plates engine, без checker-зависимостей (nginx -t и DNS-ping
     * не нужны в unit-тесте).
     */
    public function testCustomPrerenderSetupAndRewriteAreEmittedVerbatim()
    {
        $conf = new Configuration(__DIR__ . '/../configuration', 'prerender_custom');
        $conf->load();

        $engine = new \League\Plates\Engine(__DIR__ . '/../../src/Handler/templates');
        $rendered = $engine->render('nginx_default.conf', [
            'serverRoot'              => $conf->get('server.root'),
            'serverIndex'             => 'index.html',
            'serverPort'              => $conf->get('server.port'),
            'serverHost'              => '0.0.0.0',
            'prerenderEnabled'        => $conf->get('server.prerender.enabled'),
            'prerenderCacheTTL'       => $conf->get('server.prerender.cacheTtl', '1h'),
            'CDNUrl'                  => rtrim((string) $conf->get('server.prerender.cdnUrl'), '/'),
            'prerenderSetup'          => (string) $conf->get('server.prerender.setup'),
            'prerenderRewrite'        => (string) $conf->get('server.prerender.rewrite'),
            'prerenderHeaders'        => $conf->get('server.prerender.headers', []),
            'prerenderResolver'       => $conf->get('server.prerender.resolver', false),
            'headers'                 => [],
            'connProcMethod'          => 'epoll',
            'pidLocation'             => '/tmp/test.pid',
            'moduleBrotliInstalled'   => false,
            'platformSupportsAsyncIo' => false,
        ]);

        // setup из YAML появился в выводе as-is
        $this->assertStringContainsString('set $req_uri $uri;', $rendered);
        $this->assertStringContainsString('set $suffix "/index";', $rendered);
        $this->assertStringContainsString('set $suffix "/variation=$1";', $rendered);

        // rewrite-target подставлен из YAML
        $this->assertStringContainsString('rewrite .* /prerender$req_uri$suffix break;', $rendered);

        // proxy_pass — из cdnUrl
        $this->assertStringContainsString('proxy_pass "https://cdn.example.com";', $rendered);

        // удалённые поля больше не должны фигурировать в выводе
        $this->assertStringNotContainsString('CDNFilePostfix', $rendered);
        $this->assertStringNotContainsString('prerenderQueryParams', $rendered);
    }
}
