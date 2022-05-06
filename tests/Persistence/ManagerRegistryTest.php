<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

use Closure;
use Doctrine\Persistence\AbstractManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Proxy;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Tests\Persistence\Mapping\TestClassMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;

use function assert;
use function call_user_func;
use function get_class;

/**
 * @uses Doctrine\Tests\Persistence\TestObject
 *
 * @groups DCOM-270
 */
class ManagerRegistryTest extends DoctrineTestCase
{
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
            Proxy::class,
            $this->getManagerFactory()
        );
    }

    public function testGetManagerForClass(): void
    {
        self::assertInstanceOf(
            ObjectManager::class,
            $this->mr->getManagerForClass(TestObject::class)
        );
    }

    public function testGetManagerForProxiedClass(): void
    {
        self::assertInstanceOf(
            ObjectManager::class,
            $this->mr->getManagerForClass(TestObjectProxy::class)
        );
    }

    public function testGetManagerForProxyInterface(): void
    {
        self::assertNull($this->mr->getManagerForClass(Proxy::class));
    }

    public function testGetManagerForAnonymousClass(): void
    {
        self::assertNull($this->mr->getManagerForClass(get_class(new class {
        })));
    }

    public function testResetManager(): void
    {
        $manager    = $this->mr->getManager();
        $newManager = $this->mr->resetManager();

        self::assertNotSame($manager, $newManager);
    }

    public function testGetRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);

        $defaultManager = $this->mr->getManager();
        assert($defaultManager instanceof MockObject);
        $defaultManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(TestObject::class))
            ->will(self::returnValue($repository));

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
            Proxy::class,
            $this->getManagerFactory()
        );

        $repository = $this->createMock(ObjectRepository::class);

        $defaultManager = $this->mr->getManager();
        assert($defaultManager instanceof MockObject);
        $defaultManager
            ->expects(self::never())
            ->method('getRepository');

        $otherManager = $this->mr->getManager('other');
        assert($otherManager instanceof MockObject);
        $otherManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(TestObject::class))
            ->will(self::returnValue($repository));

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
            ->expects(self::never())
            ->method('getRepository');

        $otherManager = $this->mr->getManager('other');
        assert($otherManager instanceof MockObject);
        $otherManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(self::equalTo(OtherTestObject::class))
            ->will(self::returnValue($repository));

        self::assertSame($repository, $this->mr->getRepository(OtherTestObject::class));
    }

    private function getManagerFactory(): Closure
    {
        return function (string $name) {
            $mock = $this->createMock(ObjectManager::class);

            $driver   = $this->createMock(MappingDriver::class);
            $metadata = $this->createMock(ClassMetadata::class);

            $metadata
                ->expects(self::any())
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
     *
     * @psalm-param class-string $proxyInterfaceName
     */
    public function __construct(
        string $name,
        array $connections,
        array $managers,
        string $defaultConnection,
        string $defaultManager,
        string $proxyInterfaceName,
        callable $managerFactory
    ) {
        $this->managerFactory = $managerFactory;

        parent::__construct(
            $name,
            $connections,
            $managers,
            $defaultConnection,
            $defaultManager,
            $proxyInterfaceName
        );
    }

    protected function getService(string $name): object
    {
        if (! isset($this->services[$name])) {
            $this->services[$name] = call_user_func($this->managerFactory, $name);
        }

        return $this->services[$name];
    }

    protected function resetService(string $name): void
    {
        unset($this->services[$name]);
    }

    public function getAliasNamespace(string $alias): string
    {
        return __NAMESPACE__;
    }
}
