<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;

use function array_values;

class ColocatedMappingDriverTest extends TestCase
{
    public function testAddGetPaths(): void
    {
        $driver = $this->createDriver(__DIR__ . '/_files/colocated');
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
        $driver = $this->createDriver(__DIR__ . '/_files/colocated');
        self::assertSame([], $driver->getExcludePaths());

        $driver->addExcludePaths(['/test/path1', '/test/path2']);

        self::assertSame([
            '/test/path1',
            '/test/path2',
        ], $driver->getExcludePaths());
    }

    public function testGetSetFileExtension(): void
    {
        $driver = $this->createDriver(__DIR__ . '/_files/colocated');
        self::assertSame('.php', $driver->getFileExtension());

        $driver->setFileExtension('.php1');

        self::assertSame('.php1', $driver->getFileExtension());
    }

    /** @dataProvider pathProvider */
    public function testGetAllClassNames(string $path): void
    {
        $driver = $this->createDriver($path);

        $classes = $driver->getAllClassNames();

        self::assertSame([TestClass::class], $classes);
    }

    /** @return Generator<string, array{string}> */
    public function pathProvider(): Generator
    {
        yield 'straigthforward path' => [__DIR__ . '/_files/colocated'];
        yield 'winding path' => [__DIR__ . '/../Mapping/_files/colocated'];
    }

    private function createDriver(string $path): MyDriver
    {
        return new MyDriver($path);
    }
}

final class MyDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /** @param string ...$paths One or multiple paths where mapping classes can be found. */
    public function __construct(string ...$paths)
    {
        $this->addPaths(array_values($paths));
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }

    public function isTransient(string $className): bool
    {
        return $className !== TestClass::class;
    }
}
