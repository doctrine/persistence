<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\EntityFixture;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\Entity;
use Doctrine\Tests\Persistence\Mapping\_files\colocated\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;

use function sort;

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

    public function testGetSetFileRegex(): void
    {
        $driver = $this->createDriver(__DIR__ . '/_files/colocated');
        self::assertSame('/\.php$/', $driver->getFileRegex());

        $driver->setFileRegex('/\.php1$/');

        self::assertSame('/\.php1$/', $driver->getFileRegex());
        self::assertNull($driver->getFileExtension());
    }

    /** @deprecated */
    public function testGetSetFileExtension(): void
    {
        $driver = $this->createDriver(__DIR__ . '/_files/colocated');
        self::assertSame('.php', $driver->getFileExtension());

        $driver->setFileExtension('.php1');

        self::assertSame('.php1', $driver->getFileExtension());
        self::assertSame('/\.php1$/', $driver->getFileRegex());
    }

    /** @dataProvider pathProvider */
    public function testGetAllClassNames(string $path): void
    {
        $driver = $this->createDriver($path);

        $classes = $driver->getAllClassNames();

        sort($classes);
        self::assertSame([EntityFixture::class, Entity::class], $classes);
    }

    public function testGetAllClassNamesWithRegex(): void
    {
        $regex  = '/(?<!Fixture)\.php$/';
        $driver = $this->createDriver(__DIR__ . '/_files/colocated', $regex);

        $classes = $driver->getAllClassNames();

        self::assertSame(
            [Entity::class],
            $classes,
            'EntityFixture.php should be excluded by the regex, since it ends with Fixture suffix.',
        );
    }

    /** @return Generator<string, array{string}> */
    public static function pathProvider(): Generator
    {
        yield 'straigthforward path' => [__DIR__ . '/_files/colocated'];
        yield 'winding path' => [__DIR__ . '/../Mapping/_files/colocated'];
    }

    private function createDriver(string $path, string|null $fileRegex = null): MyDriver
    {
        return new MyDriver([$path], $fileRegex);
    }
}

final class MyDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /**
     * @param non-empty-list<string> $paths     One or multiple paths where mapping classes can be found.
     * @param string|null            $fileRegex The regex used to look for mapping files with.
     */
    public function __construct(array $paths, string|null $fileRegex = null)
    {
        $this->addPaths($paths);

        if ($fileRegex === null) {
            return;
        }

        $this->setFileRegex($fileRegex);
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
