<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Persistence\Mapping\MappingException;
use Error;
use PHPUnit\Framework\TestCase;

class PHPDriverTest extends TestCase
{
    public function testLoadMetadata(): void
    {
        $metadata = self::createStub(ClassMetadata::class);
        $driver   = new PHPDriver([__DIR__ . '/_files']);

        self::expectException(MappingException::class);
        self::expectExceptionMessage('The PHP mapping file "' . __DIR__ . '/_files/Doctrine.Tests.Persistence.Mapping.PHPTestEntity.php" must return a Closure that receives the ClassMetadata instance as argument.');

        $driver->loadMetadataForClass(PHPTestEntity::class, $metadata);
    }

    public function testLoadMetadataWithClosure(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())->method('getFieldNames');

        $driver = new PHPDriver([__DIR__ . '/_files']);
        $driver->loadMetadataForClass(PHPTestEntityClosure::class, $metadata);
    }

    public function testLoadMetadataClosureNotBoundToObject(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $driver   = new PHPDriver([__DIR__ . '/_files']);

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Using $this when not in object context');

        $driver->loadMetadataForClass(PHPTestIncorrectUseThis::class, $metadata);
    }

    public function testLoadMetadataClosureNotBoundToClass(): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $driver   = new PHPDriver([__DIR__ . '/_files']);

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Cannot use "static" in the global scope');

        $driver->loadMetadataForClass(PHPTestIncorrectUseStatic::class, $metadata);
    }
}

class PHPTestEntity
{
}

class PHPTestEntityClosure
{
}

class PHPTestIncorrectUseThis
{
}

class PHPTestIncorrectUseStatic
{
}
