<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Entity;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\EntityFixture;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;
use Traversable;

use function is_file;
use function sort;

class ColocatedMappingDriverTest extends TestCase
{
    public function testAddGetPaths(): void
    {
        $driver = $this->createDirectoryPathDriver(__DIR__ . '/_files/colocated');
        self::assertSame([
            __DIR__ . '/_files/colocated',
        ], $driver->getPaths());

        $driver->addPaths(['/test/path1', '/test/path2']);

        self::assertSame([
            __DIR__ . '/_files/colocated',
            '/test/path1',
            '/test/path2',
        ], $driver->getPaths());
    }

    public function testAddGetExcludePaths(): void
    {
        $driver = $this->createDirectoryPathDriver(__DIR__ . '/_files/colocated');
        self::assertSame([], $driver->getExcludePaths());

        $driver->addExcludePaths(['/test/path1', '/test/path2']);

        self::assertSame([
            '/test/path1',
            '/test/path2',
        ], $driver->getExcludePaths());
    }

    public function testGetSetFileExtension(): void
    {
        $driver = $this->createDirectoryPathDriver(__DIR__ . '/_files/colocated');
        self::assertSame('.php', $driver->getFileExtension());

        $driver->setFileExtension('.php1');

        self::assertSame('.php1', $driver->getFileExtension());
    }

    /** @dataProvider directoryPathProvider */
    public function testGetAllClassNamesForDirectory(string $dirPath): void
    {
        $driver = $this->createDirectoryPathDriver($dirPath);

        $classes = $driver->getAllClassNames();

        sort($classes);
        self::assertSame([Entity::class, EntityFixture::class], $classes);
    }

    public function testGetAllClassNamesForFilePaths(): void
    {
        $driver = $this->createFilePathsDriver([
            __DIR__ . '/_files/colocated/Entity.php',
            __DIR__ . '/_files/colocated/TestClass.php',
        ]);

        $classes = $driver->getAllClassNames();

        self::assertSame([Entity::class], $classes, 'The driver should only return the class names for the provided file path names, excluding transient class names.');
    }

    public function testGetAllClassNamesWorksBothForFilePathsAndRetroactivelyAddedDirectoryPaths(): void
    {
        $driver = $this->createFilePathsDriver([__DIR__ . '/_files/colocated/Entity.php']);

        $driver->addPaths([__DIR__ . '/_files/colocated/']);

        $classes = $driver->getAllClassNames();
        sort($classes);

        self::assertSame(
            [Entity::class, EntityFixture::class],
            $classes,
            'The driver should return class names from both the provided file path names and the retroactively added directory paths (these should not be ignored).',
        );
    }

    /** @return Generator<string, array{string}> */
    public static function directoryPathProvider(): Generator
    {
        yield 'straigthforward path' => [__DIR__ . '/_files/colocated'];
        yield 'winding path' => [__DIR__ . '/../Mapping/_files/colocated'];
    }

    private function createDirectoryPathDriver(string $dirPath): MyDriver
    {
        return new MyDriver([$dirPath]);
    }

    /** @param list<string> $filePaths */
    private function createFilePathsDriver(array $filePaths): MyDriver
    {
        return new MyDriver($filePaths);
    }
}

final class MyDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /** @param iterable<string> $paths One or multiple paths where mapping classes can be found. */
    public function __construct(iterable $paths)
    {
        $isFilePaths = $paths instanceof Traversable || ($paths !== [] && is_file($paths[0]));

        if (! $isFilePaths) {
            $this->paths = $paths;
        } else {
            $this->filePaths = $paths;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }

    public function isTransient(string $className): bool
    {
        return $className === TestClass::class;
    }
}
