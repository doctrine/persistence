<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\TestClass;
use PHPUnit\Framework\TestCase;

class AnnotationDriverTest extends TestCase
{
    use VerifyDeprecations;

    public function testGetAllClassNames(): void
    {
        $driver = new SimpleAnnotationDriver([__DIR__ . '/_files/annotation']);

        self::assertSame([TestClass::class], $driver->getAllClassNames());
    }

    public function testGetAllClassNamesLegacy(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/pull/217');

        $driver = new SimpleAnnotationDriver(new AnnotationReader(), [__DIR__ . '/_files/annotation']);

        self::assertSame([TestClass::class], $driver->getAllClassNames());
    }
}

class SimpleAnnotationDriver extends AnnotationDriver
{
    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className): bool
    {
        return $className === Entity::class;
    }
}

class LegacyAnnotationDriver extends AnnotationDriver
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
