<?php

declare(strict_types=1);

namespace Spacetab\Server\Exception;

use JetBrains\PhpStorm\Pure;
use Spacetab\Server\Inject;

final class InjectException extends InvalidArgumentException
{
    #[Pure]
    public static function invalidOptionName(string $name): self
    {
        $allow = join(',', [
            Inject::INJECT_BEFORE_SCRIPT,
            Inject::INJECT_TO_HEAD,
        ]);

        return new self((string) new Documentation(
            "`%s` is invalid option for server.config.inject. Possible two values `%s`, please choose one", $name, $allow
        ));
    }
}
