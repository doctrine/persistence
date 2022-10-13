<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\Mapping\Fixtures\Manager\Manager;
use Doctrine\Tests\Persistence\Mapping\Fixtures\Model;
use stdClass;

class DriverChainTest extends DoctrineTestCase
{
    public function testDelegateToMatchingNamespaceDriver(): void
    {
        $className     = DriverChainEntity::class;
        $classMetadata = $this->createMock(ClassMetadata::class);

        $chain = new MappingDriverChain();

        $driver1 = $this->createMock(MappingDriver::class);
        $driver1->expects(self::never())
                ->method('loadMetadataForClass');
        $driver1->expects(self::never())
                ->method('isTransient');

        $driver2 = $this->createMock(MappingDriver::class);
        $driver2->expects(self::once())
                ->method('loadMetadataForClass')
                ->with(self::equalTo($className), self::equalTo($classMetadata));
        $driver2->expects(self::once())
                ->method('isTransient')
                ->with(self::equalTo($className))
                ->willReturn(true);

        $chain->addDriver($driver1, 'Doctrine\Tests\Models\Company');
        $chain->addDriver($driver2, 'Doctrine\Tests\Persistence\Mapping');

        $chain->loadMetadataForClass($className, $classMetadata);

        self::assertTrue($chain->isTransient($className));
    }

    public function testLoadMetadataShouldThrowMappingExceptionWhenNoDelegatorWasFound(): void
    {
        $className     = DriverChainEntity::class;
        $classMetadata = $this->createMock(ClassMetadata::class);

        $chain = new MappingDriverChain();

        $this->expectException(MappingException::class);
        $chain->loadMetadataForClass($className, $classMetadata);
    }

    public function testGatherAllClassNames(): void
    {
        $chain = new MappingDriverChain();

        $driver1 = $this->createMock(MappingDriver::class);
        $driver1->expects(self::once())
                ->method('getAllClassNames')
                ->will(self::returnValue(['Doctrine\Tests\Models\Company\Foo']));

        $driver2 = $this->createMock(MappingDriver::class);
        $driver2->expects(self::once())
                ->method('getAllClassNames')
                ->will(self::returnValue(['Doctrine\Tests\ORM\Mapping\Bar', 'Doctrine\Tests\ORM\Mapping\Baz', 'FooBarBaz']));

        $chain->addDriver($driver1, 'Doctrine\Tests\Models\Company');
        $chain->addDriver($driver2, 'Doctrine\Tests\ORM\Mapping');

        self::assertSame([
            'Doctrine\Tests\Models\Company\Foo',
            'Doctrine\Tests\ORM\Mapping\Bar',
            'Doctrine\Tests\ORM\Mapping\Baz',
        ], $chain->getAllClassNames());
    }

    /** @group DDC-706 */
    public function testIsTransient(): void
    {
        $driver1 = $this->createMock(MappingDriver::class);
        $chain   = new MappingDriverChain();
        $chain->addDriver($driver1, 'Doctrine\Tests\Models\CMS');

        self::assertTrue($chain->isTransient(stdClass::class), 'stdClass isTransient');
    }

    /** @group DDC-1412 */
    public function testDefaultDriver(): void
    {
        $companyDriver    = $this->createMock(MappingDriver::class);
        $defaultDriver    = $this->createMock(MappingDriver::class);
        $entityClassName  = Model::class;
        $managerClassName = Manager::class;
        $chain            = new MappingDriverChain();

        $companyDriver->expects(self::never())
            ->method('loadMetadataForClass');
        $companyDriver->expects(self::once())
            ->method('isTransient')
            ->with(self::equalTo($managerClassName))
            ->will(self::returnValue(false));

        $defaultDriver->expects(self::never())
            ->method('loadMetadataForClass');
        $defaultDriver->expects(self::once())
            ->method('isTransient')
            ->with(self::equalTo($entityClassName))
            ->will(self::returnValue(true));

        self::assertNull($chain->getDefaultDriver());

        $chain->setDefaultDriver($defaultDriver);
        $chain->addDriver($companyDriver, 'Doctrine\Tests\Persistence\Mapping\Fixtures\Manager');

        $driver = $chain->getDefaultDriver();

        self::assertSame($defaultDriver, $driver);

        self::assertTrue($chain->isTransient($entityClassName));
        self::assertFalse($chain->isTransient($managerClassName));
    }

    public function testDefaultDriverGetAllClassNames(): void
    {
        $companyDriver = $this->createMock(MappingDriver::class);
        $defaultDriver = $this->createMock(MappingDriver::class);
        $chain         = new MappingDriverChain();

        $companyDriver->expects(self::once())
            ->method('getAllClassNames')
            ->will(self::returnValue(['Doctrine\Tests\Models\Company\Foo']));

        $defaultDriver->expects(self::once())
            ->method('getAllClassNames')
            ->will(self::returnValue(['Other\Class']));

        $chain->setDefaultDriver($defaultDriver);
        $chain->addDriver($companyDriver, 'Doctrine\Tests\Models\Company');

        $classNames = $chain->getAllClassNames();

        self::assertSame(['Doctrine\Tests\Models\Company\Foo', 'Other\Class'], $classNames);
    }
}

class DriverChainEntity
{
}
