<?php

declare(strict_types=1);

namespace Spacetab\Server\Exception;

use JetBrains\PhpStorm\Pure;

final class CheckException extends InvalidArgumentException
{
    #[Pure]
    public static function missingRequiredFiles(string $root, string $index): self
    {
        return new self((string) new Documentation(
            'Root server directory `%s` not exists, it is not a directory or index file `%s` does not exists.', $root, $index
        ));
    }

    #[Pure]
    public static function errorOccurredInProcess(int $code, string $stdout = 'None', string $stderr = 'None'): self
    {
        $message = <<<MSG
        Error occurred in started process.
         
        Code:   %d
        Stdout: %s
        Stderr: %s
        MSG;

        return new self((string) new Documentation($message, $code, $stdout, $stderr));
    }

    #[Pure]
    public static function nginxPathMustBePresent(): self
    {
        return new self((string) new Documentation(
            'NGINX binary not found. Are NGINX installed?'
        ));
    }

    #[Pure]
    public static function prerenderUrlIsNotSet(): self
    {
        return new self((string) new Documentation(
            'Prerender CDN URL not set. Check server.prerender.cdnUrl config key.'
        ));
    }

    #[Pure]
    public static function prerenderUrlIsInvalid(): self
    {
        return new self((string) new Documentation(
            'Prerender CDN URL is invalid. Check server.prerender.cdnUrl config key.'
        ));
    }

    #[Pure]
    public static function httpRequestFailed(string $error, int $code = 0, string $body = ''): self
    {
        $message = <<<MSG
        Error occurred in http request.

        Error: %s
        Code: %d
        Body: %s
        MSG;

        return new self((string) new Documentation($message, $error, $code, $body));
    }
}
