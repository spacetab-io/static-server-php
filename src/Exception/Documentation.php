<?php

declare(strict_types=1);

namespace Spacetab\Server\Exception;

use JetBrains\PhpStorm\Pure;

final class Documentation implements \Stringable
{
    private const DOCUMENTATION_URL = 'https://github.com/spacetab-io/static-server-php';
    private string $template;

    #[Pure]
    public function __construct(string $format, mixed ...$values)
    {
        $string = <<<FORMAT
        $format
        __
        Documentation URL: %s
        FORMAT;

        $this->template = sprintf($string,  ...[...$values, self::DOCUMENTATION_URL]);
    }

    public function __toString(): string
    {
        return $this->template;
    }
}
