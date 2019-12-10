<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit_Framework_MockObject_MockObject;

class StaticPHPDriverTest extends DoctrineTestCase
{
    public function testLoadMetadata()
    {
        /** @var ClassMetadata|PHPUnit_Framework_MockObject_MockObject $metadata */
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getFieldNames');

        $driver = new StaticPHPDriver([__DIR__]);
        $driver->loadMetadataForClass(TestEntity::class, $metadata);
    }

    public function testGetAllClassNames()
    {
        $driver     = new StaticPHPDriver([__DIR__]);
        $classNames = $driver->getAllClassNames();

        self::assertContains(TestEntity::class, $classNames);
    }
}

class TestEntity
{
    public static function loadMetadata($metadata)
    {
        $metadata->getFieldNames();
    }
}
