<?php

namespace Doctrine\Tests\Persistence;

use Closure;
use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use Doctrine\Persistence\AbstractManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Proxy;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\Mapping\TestClassMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

use function assert;
use function call_user_func;

use const PHP_VERSION_ID;

/**
 * @uses Doctrine\Tests\Persistence\TestObject
 *
 * @groups DCOM-270
 */
class ManagerRegistryTest extends DoctrineTestCase
{
    use VerifyDeprecations;

    /** @var TestManagerRegistry */
    private $mr;

    protected function setUp(): void
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default' => 'default_connection'],
            ['default' => 'default_manager'],
            'default',
            'default',
            ObjectManagerAware::class,
            $this->getManagerFactory()
        );
    }

    public function testGetManagerForClass(): void
    {
        self::assertNull($this->mr->getManagerForClass(TestObject::class));
    }

    public function testGetManagerForProxyInterface(): void
    {
        self::assertNull($this->mr->getManagerForClass(ObjectManagerAware::class));
    }

    public function testGetManagerForInvalidClass(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/issues/204');

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage(
            PHP_VERSION_ID < 80000 ?
            'Class Doctrine\Tests\Persistence\TestObjectInexistent does not exist' :
            'Class "Doctrine\Tests\Persistence\TestObjectInexistent" does not exist'
        );

        $this->mr->getManagerForClass('prefix:TestObjectInexistent');
    }

    public function testGetManagerForAliasedClass(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/issues/204');

        self::assertNull($this->mr->getManagerForClass('prefix:TestObject'));
    }

    public function testGetManagerForInvalidAliasedClass(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/doctrine/persistence/issues/204');

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage(
            PHP_VERSION_ID < 80000 ?
            'Class Doctrine\Tests\Persistence\TestObject:Foo does not exist' :
            'Class "Doctrine\Tests\Persistence\TestObject:Foo" does not exist'
        );

        $this->mr->getManagerForClass('prefix:TestObject:Foo');
    }

    public function testResetManager(): void
    {
        $manager    = $this->mr->getManager();
        $newManager = $this->mr->resetManager();

        self::assertInstanceOf(ObjectManager::class, $newManager);
        self::assertNotSame($manager, $newManager);
    }

    public function testGetRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);

        $defaultManager = $this->mr->getManager();
        assert($defaultManager instanceof MockObject);
        $defaultManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(TestObject::class))
            ->will($this->returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(TestObject::class));
    }

    public function testGetRepositoryWithSpecificManagerName(): void
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default' => 'default_connection'],
            ['default' => 'default_manager', 'other' => 'other_manager'],
            'default',
            'default',
            ObjectManagerAware::class,
            $this->getManagerFactory()
        );

        $repository = $this->createMock(ObjectRepository::class);

        $defaultManager = $this->mr->getManager();
        assert($defaultManager instanceof MockObject);
        $defaultManager
            ->expects($this->never())
            ->method('getRepository');

        $otherManager = $this->mr->getManager('other');
        assert($otherManager instanceof MockObject);
        $otherManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(TestObject::class))
            ->will($this->returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(TestObject::class, 'other'));
    }

    public function testGetRepositoryWithManagerDetection(): void
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default' => 'default_connection'],
            ['default' => 'default_manager', 'other' => 'other_manager'],
            'default',
            'default',
            Proxy::class,
            $this->getManagerFactory()
        );

        $repository = $this->createMock(ObjectRepository::class);

        $defaultManager = $this->mr->getManager();
        assert($defaultManager instanceof MockObject);
        $defaultManager
            ->expects($this->never())
            ->method('getRepository');

        $otherManager = $this->mr->getManager('other');
        assert($otherManager instanceof MockObject);
        $otherManager
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo(OtherTestObject::class))
            ->will($this->returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(OtherTestObject::class));
    }

    private function getManagerFactory(): Closure
    {
        return function (string $name) {
            $mock     = $this->createMock(ObjectManager::class);
            $driver   = $this->createMock(MappingDriver::class);
            $metadata = $this->createMock(ClassMetadata::class);

            $metadata
                ->expects($this->any())
                ->method('getName')
                ->willReturn($name === 'other_manager' ? OtherTestObject::class : TestObject::class);

            $mock->method('getMetadataFactory')->willReturn(new TestClassMetadataFactory($driver, $metadata));

            return $mock;
        };
    }
}

class TestManagerRegistry extends AbstractManagerRegistry
{
    /** @var object[] */
    private $services;

    /** @var callable */
    private $managerFactory;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        $name,
        array $connections,
        array $managers,
        $defaultConnection,
        $defaultManager,
        $proxyInterfaceName,
        callable $managerFactory
    ) {
        $this->managerFactory = $managerFactory;

        parent::__construct($name, $connections, $managers, $defaultConnection, $defaultManager, $proxyInterfaceName);
    }

    /**
     * {@inheritDoc}
     */
    protected function getService($name)
    {
        if (! isset($this->services[$name])) {
            $this->services[$name] = call_user_func($this->managerFactory, $name);
        }

        return $this->services[$name];
    }

    /**
     * {@inheritDoc}
     */
    protected function resetService($name): void
    {
        unset($this->services[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAliasNamespace($alias)
    {
        return __NAMESPACE__;
    }
}
