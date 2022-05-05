<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use InvalidArgumentException;
use ReflectionClass;

use function sprintf;

/**
 * Abstract implementation of the ManagerRegistry contract.
 */
abstract class AbstractManagerRegistry implements ManagerRegistry
{
    /** @var string */
    private $name;

    /** @var array<string, string> */
    private $connections;

    /** @var array<string, string> */
    private $managers;

    /** @var string */
    private $defaultConnection;

    /** @var string */
    private $defaultManager;

    /**
     * @var string
     * @psalm-var class-string
     */
    private $proxyInterfaceName;

    /**
     * @param array<string, string> $connections
     * @param array<string, string> $managers
     * @psalm-param class-string $proxyInterfaceName
     */
    public function __construct(
        string $name,
        array $connections,
        array $managers,
        string $defaultConnection,
        string $defaultManager,
        string $proxyInterfaceName
    ) {
        $this->name               = $name;
        $this->connections        = $connections;
        $this->managers           = $managers;
        $this->defaultConnection  = $defaultConnection;
        $this->defaultManager     = $defaultManager;
        $this->proxyInterfaceName = $proxyInterfaceName;
    }

    /**
     * Fetches/creates the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return ObjectManager The instance of the given service.
     */
    abstract protected function getService(string $name);

    /**
     * Resets the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return void
     */
    abstract protected function resetService(string $name);

    /**
     * Gets the name of the registry.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(?string $name = null)
    {
        if ($name === null) {
            $name = $this->defaultConnection;
        }

        if (! isset($this->connections[$name])) {
            throw new InvalidArgumentException(
                sprintf('Doctrine %s Connection named "%s" does not exist.', $this->name, $name)
            );
        }

        return $this->getService($this->connections[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
        return $this->connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections()
    {
        $connections = [];
        foreach ($this->connections as $name => $id) {
            $connections[$name] = $this->getService($id);
        }

        return $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName()
    {
        return $this->defaultManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function getManager(?string $name = null)
    {
        if ($name === null) {
            $name = $this->defaultManager;
        }

        if (! isset($this->managers[$name])) {
            throw new InvalidArgumentException(
                sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name)
            );
        }

        return $this->getService($this->managers[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getManagerForClass(string $class)
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

            if (! $manager->getMetadataFactory()->isTransient($class)) {
                return $manager;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerNames()
    {
        return $this->managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers()
    {
        $managers = [];

        foreach ($this->managers as $name => $id) {
            $manager         = $this->getService($id);
            $managers[$name] = $manager;
        }

        return $managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(
        string $persistentObject,
        ?string $persistentManagerName = null
    ) {
        return $this
            ->selectManager($persistentObject, $persistentManagerName)
            ->getRepository($persistentObject);
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager(?string $name = null)
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

    /**
     * @psalm-param class-string $persistentObject
     */
    private function selectManager(
        string $persistentObject,
        ?string $persistentManagerName = null
    ): ObjectManager {
        if ($persistentManagerName !== null) {
            return $this->getManager($persistentManagerName);
        }

        return $this->getManagerForClass($persistentObject) ?? $this->getManager();
    }
}
