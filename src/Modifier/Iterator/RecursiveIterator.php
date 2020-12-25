<?php

declare(strict_types=1);

namespace StaticServer\Modifier\Iterator;

use Amp\File\Filesystem;
use Amp\Promise;
use Spacetab\Configuration\ConfigurationAwareInterface;
use Spacetab\Configuration\ConfigurationAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function Amp\call;

final class RecursiveIterator implements IteratorInterface, ConfigurationAwareInterface, LoggerAwareInterface
{
    use ConfigurationAwareTrait, LoggerAwareTrait;

    private Filesystem $filesystem;

    /**
     * RecursiveIterator constructor.
     *
     * @param \Amp\File\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Iterate files in server.root.
     *
     * @return Promise<iterable<Transfer>>
     */
    public function iterate(): Promise
    {
        return call(fn() =>
            yield (yield from $this->fromDirectoryTreeToFlattenDTO(
                yield $this->getRootPath()
            ))
        );
    }

    /**
     * @param string $path
     * @return \Generator
     */
    private function fromDirectoryTreeToFlattenDTO(string $path): \Generator
    {
        static $promises = [];

        foreach (yield $this->filesystem->listFiles($path) as $file) {
            $location = "{$path}/{$file}";

            if (yield $this->filesystem->isDirectory($location)) {
                yield from $this->fromDirectoryTreeToFlattenDTO($location);
            } else {
                $this->logger->debug("Iterator. Processing file: {$location}");

                $promises[] = call(function () use ($file, $location, $path) {
                    $transfer = new Transfer();
                    $transfer->filename  = $file;
                    $transfer->realpath  = $location;
                    $transfer->extension = pathinfo($location, PATHINFO_EXTENSION);
                    $transfer->location  = $path;
                    $transfer->content   = yield $this->filesystem->read($location);

                    return $transfer;
                });
            }
        }

        return $promises;
    }

    /**
     * Check if server.root is exists and get realpath.
     *
     * @return Promise<string>
     */
    private function getRootPath(): Promise
    {
        $path = $this->configuration->get('server.root');

        return call(function () use ($path) {
            $check = yield [
                $this->filesystem->exists($path),
                $this->filesystem->isDirectory($path)
            ];

            // Funny check.
            if (array_filter($check, fn($x) => $x === true) === []) {
                throw new \InvalidArgumentException('Root server directory not found or it is not a directory.');
            }

            return $path;
        });
    }
}
