<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit_Framework_MockObject_MockObject;

use function assert;

class PHPDriverTest extends DoctrineTestCase
{
    public function testLoadMetadata(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        assert($metadata instanceof ClassMetadata || $metadata instanceof PHPUnit_Framework_MockObject_MockObject);
        $metadata->expects($this->once())->method('getFieldNames');

        $driver = new PHPDriver([__DIR__ . '/_files']);
        $driver->loadMetadataForClass('TestEntity', $metadata);
    }
}
