<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Entity;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;

class AnnotationDriverTest extends ColocatedMappingDriverTest
{
    protected function createDriver(string $path): MappingDriver
    {
        return new SimpleAnnotationDriver(new AnnotationReader(), $path);
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
