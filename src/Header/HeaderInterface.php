<?php

declare(strict_types=1);

namespace Spacetab\Server\Header;

interface HeaderInterface
{
    /**
     * Map headers names in config to real http headers names
     */
    public const CONFIG_MAP = [
        'pragma'                  => 'Pragma',
        'cacheControl'            => 'Cache-Control',
        'frameOptions'            => 'X-Frame-Options',
        'refererPolicy'           => 'Referrer-Policy',
        'featurePolicy'           => 'Feature-Policy',
        'contentSecurityPolicy'   => 'Content-Security-Policy',
        'xssProtection'           => 'X-XSS-Protection',
        'xContentType'            => 'X-Content-Type',
        'xContentTypeOptions'     => 'X-Content-Type-Options',
        'xUaCompatible'           => 'X-UA-Compatible',
        'strictTransportSecurity' => 'Strict-Transport-Security',
        'link'                    => 'Link',
    ];

    /**
     * Converts headers declared in Yaml configuration to real.
     * Due to backward compatibility.
     *
     * @return array<string, string>
     */
    public function convert(): array;
}
