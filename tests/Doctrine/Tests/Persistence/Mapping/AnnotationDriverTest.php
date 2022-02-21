<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\TestClass;
use Generator;
use PHPUnit\Framework\TestCase;

class AnnotationDriverTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testGetAllClassNames(string $path): void
    {
        $reader = new AnnotationReader();
        $driver = new SimpleAnnotationDriver($reader, [__DIR__ . '/_files/annotation']);

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
}

class SimpleAnnotationDriver extends AnnotationDriver
{
    /** @var array<class-string, bool|int> */
    protected $entityAnnotationClasses = [Entity::class => true];

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }
}
