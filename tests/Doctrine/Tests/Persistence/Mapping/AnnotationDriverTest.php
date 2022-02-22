<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\TestClass;
use Doctrine\Tests\Persistence\TestObject;
use Generator;
use PHPUnit\Framework\TestCase;

class AnnotationDriverTest extends TestCase
{
    /** @var AnnotationReader */
    private $reader;

    /** @var SimpleAnnotationDriver */
    private $driver;

    public function testAddGetPaths(): void
    {
        self::assertSame([
            __DIR__ . '/_files/annotation',
        ], $this->driver->getPaths());

        $this->driver->addPaths(['/test/path1', '/test/path2']);

        self::assertSame([
            __DIR__ . '/_files/annotation',
            '/test/path1',
            '/test/path2',
        ], $this->driver->getPaths());
    }

    public function testAddGetExcludePaths(): void
    {
        self::assertSame([], $this->driver->getExcludePaths());

        $this->driver->addExcludePaths(['/test/path1', '/test/path2']);

        self::assertSame([
            '/test/path1',
            '/test/path2',
        ], $this->driver->getExcludePaths());
    }

    public function testGetReader(): void
    {
        self::assertSame($this->reader, $this->driver->getReader());
    }

    public function testGetSetFileExtension(): void
    {
        self::assertSame('.php', $this->driver->getFileExtension());

        $this->driver->setFileExtension('.php1');

        self::assertSame('.php1', $this->driver->getFileExtension());
    }

    /**
     * @dataProvider pathProvider
     */
    public function testGetAllClassNames(string $path): void
    {
        $reader = new AnnotationReader();
        $driver = new SimpleAnnotationDriver($reader, $path);

        $classes = $driver->getAllClassNames();

        self::assertSame([TestClass::class], $classes);
    }

    /**
     * @return Generator<string, array{string}>
     */
    public function pathProvider(): Generator
    {
        yield 'straigthforward path' => [__DIR__ . '/_files/annotation'];
        yield 'winding path' => [__DIR__ . '/../Mapping/_files/annotation'];
    }

    public function testIsTransient(): void
    {
        self::assertTrue($this->driver->isTransient(TestObject::class));

        self::assertFalse($this->driver->isTransient(IsTransientTest::class));
    }

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader();

        $this->driver = new SimpleAnnotationDriver(
            $this->reader,
            [__DIR__ . '/_files/annotation']
        );
    }
}

class SimpleAnnotationDriver extends AnnotationDriver
{
    /** @var array<class-string, bool|int> */
    protected $entityAnnotationClasses = [Entity::class => true];

    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
    }
}

/**
 * @Entity
 */
class IsTransientTest
{
}
