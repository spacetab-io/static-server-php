<?php

declare(strict_types=1);

namespace Spacetab\Tests\Server;

use Amp\File;
use Generator;
use Spacetab\Configuration\Configuration;
use Spacetab\Server\Exception\InjectException;
use Spacetab\Server\Inject;

class InjectTest extends TestCase
{
    public function testInjectSecurityTxtFile(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        $inject = new Inject($conf);
        $this->assertNull(yield $inject->injectSecurityTxtFile());

        $actual = yield File\read(
            rtrim($conf->get('server.root'), '/') . '/.well-known/security.txt'
        );

        $this->assertStringContainsString("Contact: security@spacetab.io\nPreferred-Languages: en, ru", $actual);
    }

    public function testInjectConfigJsFile(): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', 'tests');
        $conf->load();

        $inject = new Inject($conf);
        $this->assertNull(yield $inject->injectConfigJsFile('tests', 'sha1'));

        $actual = yield File\read(
            rtrim($conf->get('server.root'), '/') . '/__config.js'
        );

        $this->assertMatchesRegularExpression('/window\.__stage=\'tests\'/', $actual);
        $this->assertMatchesRegularExpression('/window\.__config=JSON\.parse/', $actual);
        $this->assertMatchesRegularExpression('/window\.__vcs=\'sha1\'/', $actual);
        $this->assertMatchesRegularExpression('/console\.log/', $actual);
        $this->assertStringNotContainsString('"server":', $actual);
    }

    public function testHowInjectConfigJsScriptToIndexFileWorksWithStandardCase(): Generator
    {
        yield from $this->injectConfigJsToIndexFile('tests', function ($actual) {
            $this->assertStringContainsString('/__config.js', $actual);
            $this->assertStringContainsString('preload', $actual);
        });
    }

    public function testHowInjectConfigJsScriptToIndexFileWorksWithoutHeadSection(): Generator
    {
        yield from $this->injectConfigJsToIndexFile('tests_inject_head', function ($actual) {
            $this->assertStringNotContainsString('/__config.js', $actual);
        });
    }

    public function testHowInjectConfigJsScriptToIndexFileWorksWithInvalidValue(): Generator
    {
        $this->expectException(InjectException::class);
        yield from $this->injectConfigJsToIndexFile('inject_invalid', fn() => null);
    }

    public function testHowInjectConfigJsScriptToIndexFileWorksWithEmptyHeadSection(): Generator
    {
        yield from $this->injectConfigJsToIndexFile('tests_inject_head', function ($actual) {
            $this->assertStringNotContainsString('__config', $actual);
            $this->assertStringNotContainsString('preload', $actual);
        });
    }

    public function testHowInjectConfigJsScriptToIndexFileWorksWithoutScriptTag(): Generator
    {
        yield from $this->injectConfigJsToIndexFile('empty_index', function ($actual) {
            $this->assertStringNotContainsString('__config', $actual);
            $this->assertStringNotContainsString('preload', $actual);
        });
    }

    public function testHowInjectConfigJsScriptToIndexFilePreloading(): Generator
    {
        yield from $this->injectConfigJsToIndexFile('tests', function ($actual) {
            $this->assertStringContainsString('preload', $actual);
        });

        yield from $this->injectConfigJsToIndexFile('head_link_not_exists', function ($actual) {
            $this->assertStringContainsString('preload', $actual);
        });
    }

    private function injectConfigJsToIndexFile(string $config, callable $callback): Generator
    {
        $conf = new Configuration(__DIR__ . '/configuration', $config);
        $conf->load();

        $handler = new Inject($conf);
        yield $handler->injectConfigJsScriptToIndexFile();

        $actual = yield File\read(
            rtrim($conf->get('server.root'), '/') . '/index.html'
        );

        $callback($actual);
    }
}
