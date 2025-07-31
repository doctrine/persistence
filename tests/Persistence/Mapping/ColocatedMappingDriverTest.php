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

use function assert;
use function is_array;
use function sort;

class ColocatedMappingDriverTest extends TestCase
{
    public function testAddGetPaths(): void
    {
        $driver = $this->createPathDriver(__DIR__ . '/_files/colocated');
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
        $driver = $this->createPathDriver(__DIR__ . '/_files/colocated');
        self::assertSame([], $driver->getExcludePaths());

        $driver->addExcludePaths(['/test/path1', '/test/path2']);

        self::assertSame([
            '/test/path1',
            '/test/path2',
        ], $driver->getExcludePaths());
    }

    public function testGetSetFileExtension(): void
    {
        $driver = $this->createPathDriver(__DIR__ . '/_files/colocated');
        self::assertSame('.php', $driver->getFileExtension());

        $driver->setFileExtension('.php1');

        self::assertSame('.php1', $driver->getFileExtension());
    }

    /** @dataProvider pathProvider */
    public function testGetAllClassNamesForPath(string $path): void
    {
        $driver = $this->createPathDriver($path);

        $classes = $driver->getAllClassNames();

        sort($classes);
        self::assertSame([Entity::class, EntityFixture::class], $classes);
    }

    public function testGetAllClassNamesForIterableFilePathNames(): void
    {
        $driver = $this->createFilePathNamesDriver([
            __DIR__ . '/_files/colocated/Entity.php',
            __DIR__ . '/_files/colocated/TestClass.php',
        ]);

        $classes = $driver->getAllClassNames();

        self::assertSame([Entity::class], $classes, 'The driver should only return the class names from the provided file path names, excluding transient class names.');
    }

    /** @return Generator<string, array{string}> */
    public static function pathProvider(): Generator
    {
        yield 'straigthforward path' => [__DIR__ . '/_files/colocated'];
        yield 'winding path' => [__DIR__ . '/../Mapping/_files/colocated'];
    }

    private function createPathDriver(string $path): MyDriver
    {
        return new MyDriver([$path]);
    }

    /** @param list<string> $paths */
    private function createFilePathNamesDriver(array $paths): MyDriver
    {
        return new MyDriver($paths, true);
    }
}

final class MyDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /** @param iterable<string> $paths Source file path names */
    public function __construct(iterable $paths, bool $sourceFilePathNames = false)
    {
        if (! $sourceFilePathNames) {
            assert(is_array($paths));

            $this->addPaths($paths);
        } else {
            $this->sourceFilePathNames = $paths;
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
