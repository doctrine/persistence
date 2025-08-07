<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Generator;
use IteratorAggregate;
use SplFileInfo;

/** @implements IteratorAggregate<array-key,string> */
final class FilePathNameIterator implements IteratorAggregate
{
    public function __construct(
        /** @var iterable<SplFileInfo> */
        private readonly iterable $filesIterator,
    ) {
    }

    /** @return Generator<array-key,string> */
    public function getIterator(): Generator
    {
        foreach ($this->filesIterator as $key => $file) {
            yield $key => $file->getPathname();
        }
    }
}
