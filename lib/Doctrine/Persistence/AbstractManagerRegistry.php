<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use InvalidArgumentException;
use ReflectionClass;
use function explode;
use function sprintf;
use function strpos;

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

    /** @var string */
    private $proxyInterfaceName;

    /**
     * @param array<string, string> $connections
     * @param array<string, string> $managers
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
     * @return object The instance of the given service.
     */
    abstract protected function getService(string $name) : object;

    /**
     * Resets the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     */
    abstract protected function resetService(string $name) : void;

    /**
     * Gets the name of the registry.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(?string $name = null) : object
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
    public function getConnectionNames() : array
    {
        return $this->connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections() : array
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
    public function getDefaultConnectionName() : string
    {
        return $this->defaultConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName() : string
    {
        return $this->defaultManager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function getManager(?string $name = null) : ObjectManager
    {
        if ($name === null) {
            $name = $this->defaultManager;
        }

        if (! isset($this->managers[$name])) {
            throw new InvalidArgumentException(
                sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name)
            );
        }

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getService($this->managers[$name]);

        return $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass(string $class) : ?ObjectManager
    {
        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            [$namespaceAlias, $simpleClassName] = explode(':', $class, 2);

            $class = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new ReflectionClass($class);

        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            $parentClass = $proxyClass->getParentClass();

            if ($parentClass === false) {
                return null;
            }

            $class = $parentClass->getName();
        }

        if($this->defaultManager) {
            /** @var ObjectManager $manager */
          $manager = $this->getManager($this->defaultManager);

          if (! $manager->getMetadataFactory()->isTransient($class)) {
            return $manager;
          }
        }

        foreach ($this->managers as $id) {
            /** @var ObjectManager $manager */
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
    public function getManagerNames() : array
    {
        return $this->managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers() : array
    {
        $managers = [];

        foreach ($this->managers as $name => $id) {
            /** @var ObjectManager $manager */
            $manager = $this->getService($id);

            $managers[$name] = $manager;
        }

        return $managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(
        string $persistentObjectName,
        ?string $persistentManagerName = null
    ) : ObjectRepository {
        return $this
            ->selectManager($persistentObjectName, $persistentManagerName)
            ->getRepository($persistentObjectName);
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager(?string $name = null) : ObjectManager
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

    private function selectManager(
        string $persistentObjectName,
        ?string $persistentManagerName = null
    ) : ObjectManager {
        if ($persistentManagerName !== null) {
            return $this->getManager($persistentManagerName);
        }

        return $this->getManagerForClass($persistentObjectName) ?? $this->getManager();
    }
}
