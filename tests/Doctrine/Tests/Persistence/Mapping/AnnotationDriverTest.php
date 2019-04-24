<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\TestClass;
use Doctrine\Tests\Persistence\TestObject;
use PHPUnit\Framework\TestCase;

class AnnotationDriverTest extends TestCase
{
    /** @var AnnotationReader */
    private $reader;

    /** @var SimpleAnnotationDriver */
    private $driver;

    public function testAddGetPaths() : void
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

    public function testAddGetExcludePaths() : void
    {
        self::assertSame([], $this->driver->getExcludePaths());

        $this->driver->addExcludePaths(['/test/path1', '/test/path2']);

        self::assertSame([
            '/test/path1',
            '/test/path2',
        ], $this->driver->getExcludePaths());
    }

    public function testGetReader() : void
    {
        self::assertSame($this->reader, $this->driver->getReader());
    }

    public function testGetSetFileExtension() : void
    {
        self::assertSame('.php', $this->driver->getFileExtension());

        $this->driver->setFileExtension('.php1');

        self::assertSame('.php1', $this->driver->getFileExtension());
    }

    public function testGetAllClassNames() : void
    {
        $classes = $this->driver->getAllClassNames();

        self::assertSame([TestClass::class], $classes);
    }

    public function testIsTransient() : void
    {
        self::assertTrue($this->driver->isTransient(TestObject::class));

        self::assertFalse($this->driver->isTransient(IsTransientTest::class));
    }

    protected function setUp() : void
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
    /** @var array<string, int> */
    protected $entityAnnotationClasses = [Entity::class => 1];

    public function loadMetadataForClass(string $className, ClassMetadata $metadata) : void
    {
    }
}

/**
 * @Entity
 */
class IsTransientTest
{
}
