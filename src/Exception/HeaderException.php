<?php

declare(strict_types=1);

namespace Spacetab\Server\Exception;

use JetBrains\PhpStorm\Pure;
use Spacetab\Server\Header\HeaderInterface;

final class HeaderException extends InvalidArgumentException
{
    #[Pure]
    public static function invalidHeaderNameParameterPassed(string $name): self
    {
        $allow = join(',', array_keys(HeaderInterface::CONFIG_MAP));

        return new self((string) new Documentation(
            "Invalid header name parameter passed (%s). Allowed names: %s", $name, $allow
        ));
    }

    #[Pure]
    public static function invalidHeaderFormat(): self
    {
        return new self((string) new Documentation(
            'Invalid header format, see docs & examples.'
        ));
    }
}
