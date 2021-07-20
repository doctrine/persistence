<?php

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Tests\DoctrineTestCase;

class PHPDriverTest extends DoctrineTestCase
{
    public function testLoadMetadata(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getFieldNames');

        $driver = new PHPDriver([__DIR__ . '/_files']);
        $driver->loadMetadataForClass(PHPTestEntity::class, $metadata);
    }
    
    public function testLoadMetadataWithFunction(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getFieldNames');
        
        $driver = new PHPDriver([__DIR__ . '/_files']);
        $driver->loadMetadataForClass(PHPStaticTestEntity::class, $metadata);
    }
}

class PHPTestEntity
{
}

class PHPStaticTestEntity
{
}
