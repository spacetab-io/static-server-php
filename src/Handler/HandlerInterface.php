<?php

declare(strict_types=1);

namespace Spacetab\Server\Handler;

use Amp\Promise;

interface HandlerInterface
{
    public const HANDLER_NGINX = 'nginx';

    /**
     * Generates web server configuration.
     *
     * @return Promise<null>
     */
    public function handle(): Promise;
}
