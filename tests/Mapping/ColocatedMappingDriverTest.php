<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ClassLocator;
use Doctrine\Persistence\Mapping\Driver\ClassNames;
use Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Entity;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\EntityFixture;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\TestClass;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

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

    #[DataProvider('directoryPathProvider')]
    public function testGetAllClassNamesForDirectory(string $dirPath): void
    {
        $driver = $this->createDirectoryPathDriver($dirPath);

        $classes = $driver->getAllClassNames();

        sort($classes);
        self::assertSame([Entity::class, EntityFixture::class], $classes);
    }

    public function testGetAllClassNamesRemovesTransient(): void
    {
        $driver = $this->createClassNamesDriver([
            // This class is transient, so it should not be returned by getAllClassNames()
            // placed before the Entity class to validate that the driver returns an
            // array without gaps in the indexes
            TestClass::class,
            Entity::class,
        ]);

        $classes = $driver->getAllClassNames();

        self::assertSame([Entity::class], $classes, 'The driver should only return the class names for the provided file path names, excluding transient class names.');
    }

    public function testGetAllClassNamesWorksBothForFilePathsAndRetroactivelyAddedDirectoryPaths(): void
    {
        $driver = $this->createClassNamesDriver([Entity::class]);

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

    /** @param list<class-string> $classes */
    private function createClassNamesDriver(array $classes): MyDriver
    {
        return new MyDriver(new ClassNames($classes));
    }
}

final class MyDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /** @param string[]|ClassLocator $paths One or multiple paths where mapping classes can be found. */
    public function __construct(array|ClassLocator $paths)
    {
        if ($paths instanceof ClassLocator) {
            $this->classLocator = $paths;
        } else {
            $this->paths = $paths;
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
