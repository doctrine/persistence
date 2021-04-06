<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\TestClass;
use PHPUnit\Framework\TestCase;

class AnnotationDriverTest extends TestCase
{
    public function testGetAllClassNames(): void
    {
        $reader = new AnnotationReader();
        $driver = new SimpleAnnotationDriver($reader, [__DIR__ . '/_files/annotation']);

        $classes = $driver->getAllClassNames();

        self::assertSame([TestClass::class], $classes);
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
