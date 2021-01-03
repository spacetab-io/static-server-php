<?php

declare(strict_types=1);

namespace Spacetab\Tests\Server\Header;

use Spacetab\Configuration\Configuration;
use Spacetab\Server\Exception\HeaderException;
use Spacetab\Server\Header\ConvertsHeader;
use Spacetab\Tests\Server\TestCase;

class HeaderTest extends TestCase
{
    public function testHeaderConverter()
    {
        $conf = new Configuration(__DIR__ . '/../configuration', 'nested');
        $conf->load();

        $ch = new ConvertsHeader($conf);
        $headers = $ch->convert();

        $featurePolicy = "geolocation 'none'; payment 'none'; microphone 'none'; camera 'none'; autoplay 'none'";
        $link = '<https://example.com/font.woff2>; rel=preload; as=font; type="font/woff2", <https://example.com/app/script.js>; rel=preload; as=script';

        $this->assertSame($featurePolicy, $headers['Feature-Policy']);
        $this->assertSame($link, $headers['Link']);
        $this->assertSame('1; mode=block', $headers['X-XSS-Protection']);
    }

    public function testHowInvalidHeaderNameCheckWorks()
    {
        $conf = new Configuration(__DIR__ . '/../configuration', 'invalid_header_name');
        $conf->load();

        $this->expectException(HeaderException::class);
        $this->expectExceptionMessageMatches('/Invalid header name parameter passed.*/');

        $ch = new ConvertsHeader($conf);
        $ch->convert();
    }

    public function testHowInvalidHeaderValueCheckWorks()
    {
        $conf = new Configuration(__DIR__ . '/../configuration', 'invalid_header_value');
        $conf->load();

        $this->expectException(HeaderException::class);
        $this->expectExceptionMessageMatches('/Invalid header format.*/');

        $h = new ConvertsHeader($conf);
        $h->convert();
    }
}
