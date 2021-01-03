<?php

declare(strict_types=1);

namespace Spacetab\Server\Exception;

use JetBrains\PhpStorm\Pure;
use Spacetab\Server\Handler\HandlerInterface;

final class HandlerException extends InvalidArgumentException
{
    #[Pure]
    public static function handlerNotSupported(string $name): self
    {
        $allow = join(',', [
            HandlerInterface::HANDLER_NGINX
        ]);

        return new self((string) new Documentation(
            "`%s` – handler not supported to generate configuration for webserver. Allowed values: %s", $name, $allow
        ));
    }
}
