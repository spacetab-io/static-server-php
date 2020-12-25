<?php

declare(strict_types=1);

namespace StaticServer\Modifier\Iterator;

use Amp\Promise;

interface IteratorInterface
{
    /**
     * Iterate files in server.root.
     *
     * @return Promise<iterable<\StaticServer\Modifier\Iterator\Transfer>>
     */
    public function iterate(): Promise;
}
