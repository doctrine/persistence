<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PHPDriverTest extends DoctrineTestCase
{
    public function testLoadMetadata() : void
    {
        /** @var ClassMetadata|MockObject $metadata */
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())->method('getFieldNames');

        $driver = new PHPDriver([__DIR__ . '/_files']);
        $driver->loadMetadataForClass('TestEntity', $metadata);
    }
}
