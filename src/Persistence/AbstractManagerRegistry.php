<?php

namespace Doctrine\Persistence;

use Doctrine\Deprecations\Deprecation;
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

    /** @var string[] */
    private $connections;

    /** @var string[] */
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
     * @param string   $name
     * @param string[] $connections
     * @param string[] $managers
     * @param string   $defaultConnection
     * @param string   $defaultManager
     * @param string   $proxyInterfaceName
     * @psalm-param class-string $proxyInterfaceName
     */
    public function __construct($name, array $connections, array $managers, $defaultConnection, $defaultManager, $proxyInterfaceName)
    {
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
    abstract protected function getService($name);

    /**
     * Resets the given services.
     *
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return void
     */
    abstract protected function resetService($name);

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
    public function getConnection($name = null)
    {
        if ($name === null) {
            $name = $this->defaultConnection;
        }

        if (! isset($this->connections[$name])) {
            throw new InvalidArgumentException(sprintf('Doctrine %s Connection named "%s" does not exist.', $this->name, $name));
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
    public function getManager($name = null)
    {
        if ($name === null) {
            $name = $this->defaultManager;
        }

        if (! isset($this->managers[$name])) {
            throw new InvalidArgumentException(sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name));
        }

        return $this->getService($this->managers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass($class)
    {
        $className = $this->getRealClassName($class);

        $proxyClass = new ReflectionClass($className);

        if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
            $parentClass = $proxyClass->getParentClass();

            if (! $parentClass) {
                return null;
            }

            $className = $parentClass->getName();
        }

        foreach ($this->managers as $id) {
            $manager = $this->getService($id);

            if (! $manager->getMetadataFactory()->isTransient($className)) {
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
        $dms = [];
        foreach ($this->managers as $name => $id) {
            $dms[$name] = $this->getService($id);
        }

        return $dms;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this
            ->selectManager($persistentObject, $persistentManagerName)
            ->getRepository($persistentObject);
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null)
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
     * @psalm-param class-string $persistentObjectName
     */
    private function selectManager(string $persistentObjectName, ?string $persistentManagerName = null): ObjectManager
    {
        if ($persistentManagerName !== null) {
            return $this->getManager($persistentManagerName);
        }

        return $this->getManagerForClass($persistentObjectName) ?? $this->getManager();
    }

    /**
     * @psalm-return class-string
     */
    private function getRealClassName(string $classNameOrAlias): string
    {
        // Check for namespace alias
        if (strpos($classNameOrAlias, ':') !== false) {
            Deprecation::trigger(
                'doctrine/persistence',
                'https://github.com/doctrine/persistence/issues/204',
                'Short namespace aliases such as "%s" are deprecated, use ::class constant instead.',
                $classNameOrAlias
            );

            [$namespaceAlias, $simpleClassName] = explode(':', $classNameOrAlias, 2);

            /** @psalm-var class-string */
            return $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        /** @psalm-var class-string */
        return $classNameOrAlias;
    }
}
