<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Driver\DirectoryFilesIterator;
use Doctrine\Persistence\Mapping\MappingException;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function array_map;
use function iterator_to_array;
use function reset;

/** @covers \Doctrine\Persistence\Mapping\Driver\DirectoryFilesIterator */
final class DirectoryFilesIteratorTest extends TestCase
{
    public function testIteratorFindsPhpFiles(): void
    {
        $iterator = new DirectoryFilesIterator([__DIR__ . '/_dir_iterator']);
        $files    = iterator_to_array($iterator);

        self::assertCount(2, $files);
        self::assertContainsOnlyInstancesOf(SplFileInfo::class, $files);

        $fileNames = array_map(static fn (SplFileInfo $file): string => $file->getFilename(), $files);
        self::assertContains('first.php', $fileNames);
        self::assertContains('second.php', $fileNames);
    }

    public function testIteratorFindsFilesWithCustomExtension(): void
    {
        $iterator = new DirectoryFilesIterator([__DIR__ . '/_dir_iterator'], '.mphp');
        $files    = iterator_to_array($iterator);

        self::assertCount(1, $files);
        self::assertContainsOnlyInstancesOf(SplFileInfo::class, $files);

        self::assertSame('first.mphp', reset($files)->getFilename());
    }

    public function testIteratorThrowsExceptionForNonExistentDirectory(): void
    {
        $iterator = new DirectoryFilesIterator(['/path/does/not/exist']);

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('drivers must have a valid directory path');

        iterator_to_array($iterator);
    }
}
