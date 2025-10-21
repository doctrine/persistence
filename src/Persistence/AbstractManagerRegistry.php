<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use InvalidArgumentException;
use ReflectionClass;

use function assert;
use function sprintf;

/**
 * Abstract implementation of the ManagerRegistry contract.
 */
abstract class AbstractManagerRegistry implements ManagerRegistry
{
    /**
     * @param array<string, string> $connections
     * @param array<string, string> $managers
     * @phpstan-param class-string $proxyInterfaceName
     */
    public function __construct(
        private readonly string $name,
        private array $connections,
        private array $managers,
        private readonly string $defaultConnection,
        private readonly string $defaultManager,
        private readonly string $proxyInterfaceName,
    ) {
    }

    /**
     * Fetches/creates the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return object The instance of the given service.
     */
    abstract protected function getService(string $name): object;

    /**
     * Resets the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     */
    abstract protected function resetService(string $name): void;

    /** Gets the name of the registry. */
    public function getName(): string
    {
        return $this->name;
    }

    public function getConnection(string|null $name = null): object
    {
        if ($name === null) {
            $name = $this->defaultConnection;
        }

        if (! isset($this->connections[$name])) {
            throw new InvalidArgumentException(
                sprintf('Doctrine %s Connection named "%s" does not exist.', $this->name, $name),
            );
        }

        return $this->getService($this->connections[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getConnectionNames(): array
    {
        return $this->connections;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnections(): array
    {
        $connections = [];
        foreach ($this->connections as $name => $id) {
            $connections[$name] = $this->getService($id);
        }

        return $connections;
    }

    public function getDefaultConnectionName(): string
    {
        return $this->defaultConnection;
    }

    public function getDefaultManagerName(): string
    {
        return $this->defaultManager;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function getManager(string|null $name = null): ObjectManager
    {
        if ($name === null) {
            $name = $this->defaultManager;
        }

        if (! isset($this->managers[$name])) {
            throw new InvalidArgumentException(
                sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name),
            );
        }

        $service = $this->getService($this->managers[$name]);
        assert($service instanceof ObjectManager);

        return $service;
    }

    public function getManagerForClass(string $class): ObjectManager|null
    {
        $proxyClass = new ReflectionClass($class);
        if ($proxyClass->isAnonymous()) {
            return null;
        }

        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            $parentClass = $proxyClass->getParentClass();

            if ($parentClass === false) {
                return null;
            }

            $class = $parentClass->getName();
        }

        foreach ($this->managers as $id) {
            $manager = $this->getService($id);
            assert($manager instanceof ObjectManager);

            if (! $manager->getMetadataFactory()->isTransient($class)) {
                return $manager;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getManagerNames(): array
    {
        return $this->managers;
    }

    /**
     * {@inheritDoc}
     */
    public function getManagers(): array
    {
        $managers = [];

        foreach ($this->managers as $name => $id) {
            $manager = $this->getService($id);
            assert($manager instanceof ObjectManager);
            $managers[$name] = $manager;
        }

        return $managers;
    }

    public function getRepository(
        string $persistentObject,
        string|null $persistentManagerName = null,
    ): ObjectRepository {
        return $this
            ->selectManager($persistentObject, $persistentManagerName)
            ->getRepository($persistentObject);
    }

    public function resetManager(string|null $name = null): ObjectManager
    {
        if ($name === null) {
            $name = $this->defaultManager;
        }

        if (! isset($this->managers[$name])) {
            throw new InvalidArgumentException(sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name));
        }

        // force the creation of a new document manager
        // if the current one is closed
        $this->resetService($this->managers[$name]);

        return $this->getManager($name);
    }

    /** @phpstan-param class-string $persistentObject */
    private function selectManager(
        string $persistentObject,
        string|null $persistentManagerName = null,
    ): ObjectManager {
        if ($persistentManagerName !== null) {
            return $this->getManager($persistentManagerName);
        }

        return $this->getManagerForClass($persistentObject) ?? $this->getManager();
    }
}
