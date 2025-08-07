<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Driver\FilePathNameIterator;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function iterator_to_array;

/** @covers \Doctrine\Persistence\Mapping\Driver\FilePathNameIterator */
final class FilePathNameIteratorTest extends TestCase
{
    public function testIteratorReturnsFilePaths(): void
    {
        $iterator = new FilePathNameIterator([
            1 => $this->file('/first-path'),
            2 => $this->file('/second-path'),
        ]);

        self::assertSame(
            [
                1 => '/first-path',
                2 => '/second-path',
            ],
            iterator_to_array($iterator),
        );
    }

    private function file(string $pathName): SplFileInfo
    {
        $file = $this->createStub(SplFileInfo::class);

        $file->method('getPathname')
            ->willReturn($pathName);

        return $file;
    }
}
