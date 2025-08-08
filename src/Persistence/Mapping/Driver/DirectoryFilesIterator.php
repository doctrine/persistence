<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use AppendIterator;
use Doctrine\Persistence\Mapping\MappingException;
use FilesystemIterator;
use Iterator;
use IteratorAggregate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

use function is_dir;
use function preg_quote;
use function sprintf;

/** @implements IteratorAggregate<array-key,SplFileInfo> */
final class DirectoryFilesIterator implements IteratorAggregate
{
    public function __construct(
        /** @var list<string> */
        private readonly array $paths,
        private readonly string $fileExtension = '.php',
    ) {
    }

    /**
     * @return Iterator<array-key,SplFileInfo>
     *
     * @throws MappingException
     */
    public function getIterator(): Iterator
    {
        /** @var AppendIterator<array-key,SplFileInfo,Iterator<array-key,SplFileInfo>> $filesIterator */
        $filesIterator = new AppendIterator();

        foreach ($this->paths as $path) {
            if (! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            /** @var Iterator<array-key,SplFileInfo> $iterator */
            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY,
                ),
                sprintf('/%s$/', preg_quote($this->fileExtension, '/')),
                RegexIterator::MATCH,
            );

            $filesIterator->append($iterator);
        }

        return $filesIterator;
    }
}
