<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit_Framework_MockObject_MockObject;

use function assert;

class StaticPHPDriverTest extends DoctrineTestCase
{
    public function testLoadMetadata(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        assert($metadata instanceof ClassMetadata || $metadata instanceof PHPUnit_Framework_MockObject_MockObject);
        $metadata->expects($this->once())->method('getFieldNames');

        $driver = new StaticPHPDriver([__DIR__]);
        $driver->loadMetadataForClass(TestEntity::class, $metadata);
    }

    public function testGetAllClassNames(): void
    {
        $driver     = new StaticPHPDriver([__DIR__]);
        $classNames = $driver->getAllClassNames();

        self::assertContains(TestEntity::class, $classNames);
    }
}

class TestEntity
{
    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $metadata->getFieldNames();
    }
}
