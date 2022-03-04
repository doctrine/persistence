<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\ColocatedMappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;

class ColocatedMappingDriverTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testGetAllClassNames(string $path): void
    {
        $driver = $this->createDriver($path);

        $classes = $driver->getAllClassNames();

        self::assertSame([TestClass::class], $classes);
    }

    /**
     * @return Generator<string, array{string}>
     */
    public function pathProvider(): Generator
    {
        yield 'straigthforward path' => [__DIR__ . '/_files/colocated'];
        yield 'winding path' => [__DIR__ . '/../Mapping/_files/colocated'];
    }

    protected function createDriver(string $path): MappingDriver
    {
        return new MyDriver($path);
    }
}

final class MyDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /**
     * @param string ...$paths One or multiple paths where mapping classes can be found.
     */
    public function __construct(string ...$paths)
    {
        $this->addPaths($paths);
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient($className): bool
    {
        return $className !== TestClass::class;
    }
}
