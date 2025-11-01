<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Tests\DoctrineTestCase;
use Error;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\TestWith;

class PHPDriverTest extends DoctrineTestCase
{
    use VerifyDeprecations;

    /** @phpstan-param class-string $className */
    #[IgnoreDeprecations]
    #[TestWith([PHPTestEntity::class])]
    #[TestWith([PHPTestEntityAssert::class])]
    public function testLoadMetadata(string $className): void
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())->method('getFieldNames');
        $driver = new PHPDriver([__DIR__ . '/_files']);

        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/pull/450');
        $driver->loadMetadataForClass($className, $metadata);
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

class PHPTestEntityAssert
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
