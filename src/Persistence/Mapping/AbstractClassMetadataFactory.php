<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Proxy;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionClass;
use ReflectionException;

use function array_combine;
use function array_keys;
use function array_map;
use function array_reverse;
use function array_unshift;
use function assert;
use function class_exists;
use function ltrim;
use function str_replace;
use function strpos;
use function strrpos;
use function substr;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping informations of a class which describes how a class should be mapped
 * to a relational database.
 *
 * This class was abstracted from the ORM ClassMetadataFactory.
 *
 * @template CMTemplate of ClassMetadata
 * @template-implements ClassMetadataFactory<CMTemplate>
 */
abstract class AbstractClassMetadataFactory implements ClassMetadataFactory
{
    /**
     * Salt used by specific Object Manager implementation.
     *
     * @var string
     */
    protected $cacheSalt = '__CLASSMETADATA__';

    /** @var CacheItemPoolInterface|null */
    private $cache;

    /**
     * @var array<string, ClassMetadata>
     * @psalm-var CMTemplate[]
     */
    private $loadedMetadata = [];

    /** @var bool */
    protected $initialized = false;

    /** @var ReflectionService|null */
    private $reflectionService = null;

    /** @var ProxyClassNameResolver|null */
    private $proxyClassNameResolver = null;

    public function setCache(CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }

    final protected function getCache(): ?CacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * Returns an array of all the loaded metadata currently in memory.
     *
     * @return ClassMetadata[]
     * @psalm-return CMTemplate[]
     */
    public function getLoadedMetadata()
    {
        return $this->loadedMetadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllMetadata()
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        $driver   = $this->getDriver();
        $metadata = [];
        foreach ($driver->getAllClassNames() as $className) {
            $metadata[] = $this->getMetadataFor($className);
        }

        return $metadata;
    }

    public function setProxyClassNameResolver(ProxyClassNameResolver $resolver): void
    {
        $this->proxyClassNameResolver = $resolver;
    }

    /**
     * Lazy initialization of this stuff, especially the metadata driver,
     * since these are not needed at all when a metadata cache is active.
     *
     * @return void
     */
    abstract protected function initialize();

    /**
     * Returns the mapping driver implementation.
     *
     * @return MappingDriver
     */
    abstract protected function getDriver();

    /**
     * Wakes up reflection after ClassMetadata gets unserialized from cache.
     *
     * @psalm-param CMTemplate $class
     *
     * @return void
     */
    abstract protected function wakeupReflection(
        ClassMetadata $class,
        ReflectionService $reflService
    );

    /**
     * Initializes Reflection after ClassMetadata was constructed.
     *
     * @psalm-param CMTemplate $class
     *
     * @return void
     */
    abstract protected function initializeReflection(
        ClassMetadata $class,
        ReflectionService $reflService
    );

    /**
     * Checks whether the class metadata is an entity.
     *
     * This method should return false for mapped superclasses or embedded classes.
     *
     * @psalm-param CMTemplate $class
     *
     * @return bool
     */
    abstract protected function isEntity(ClassMetadata $class);

    /**
     * Removes the prepended backslash of a class string to conform with how php outputs class names
     *
     * @psalm-param class-string $className
     *
     * @psalm-return class-string
     */
    private function normalizeClassName(string $className): string
    {
        return ltrim($className, '\\');
    }

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     * @throws MappingException
     */
    public function getMetadataFor(string $className)
    {
        $className = $this->normalizeClassName($className);

        if (isset($this->loadedMetadata[$className])) {
            return $this->loadedMetadata[$className];
        }

        if (class_exists($className, false) && (new ReflectionClass($className))->isAnonymous()) {
            throw MappingException::classIsAnonymous($className);
        }

        if (! class_exists($className, false) && strpos($className, ':') !== false) {
            throw MappingException::nonExistingClass($className);
        }

        $realClassName = $this->getRealClass($className);

        if (isset($this->loadedMetadata[$realClassName])) {
            // We do not have the alias name in the map, include it
            return $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];
        }

        try {
            if ($this->cache !== null) {
                $cached = $this->cache->getItem($this->getCacheKey($realClassName))->get();
                if ($cached instanceof ClassMetadata) {
                    /** @psalm-var CMTemplate $cached */
                    $this->loadedMetadata[$realClassName] = $cached;

                    $this->wakeupReflection($cached, $this->getReflectionService());
                } else {
                    $loadedMetadata = $this->loadMetadata($realClassName);
                    $classNames     = array_combine(
                        array_map([$this, 'getCacheKey'], $loadedMetadata),
                        $loadedMetadata
                    );

                    foreach ($this->cache->getItems(array_keys($classNames)) as $item) {
                        if (! isset($classNames[$item->getKey()])) {
                            continue;
                        }

                        $item->set($this->loadedMetadata[$classNames[$item->getKey()]]);
                        $this->cache->saveDeferred($item);
                    }

                    $this->cache->commit();
                }
            } else {
                $this->loadMetadata($realClassName);
            }
        } catch (MappingException $loadingException) {
            $fallbackMetadataResponse = $this->onNotFoundMetadata($realClassName);

            if ($fallbackMetadataResponse === null) {
                throw $loadingException;
            }

            $this->loadedMetadata[$realClassName] = $fallbackMetadataResponse;
        }

        if ($className !== $realClassName) {
            // We do not have the alias name in the map, include it
            $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];
        }

        return $this->loadedMetadata[$className];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetadataFor(string $className)
    {
        $className = $this->normalizeClassName($className);

        return isset($this->loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     *
     * @psalm-param class-string $className
     * @psalm-param CMTemplate $class
     *
     * @return void
     */
    public function setMetadataFor(string $className, ClassMetadata $class)
    {
        $this->loadedMetadata[$this->normalizeClassName($className)] = $class;
    }

    /**
     * Gets an array of parent classes for the given entity class.
     *
     * @psalm-param class-string $name
     *
     * @return string[]
     * @psalm-return list<class-string>
     */
    protected function getParentClasses(string $name)
    {
        // Collect parent classes, ignoring transient (not-mapped) classes.
        $parentClasses = [];

        foreach (array_reverse($this->getReflectionService()->getParentClasses($name)) as $parentClass) {
            if ($this->getDriver()->isTransient($parentClass)) {
                continue;
            }

            $parentClasses[] = $parentClass;
        }

        return $parentClasses;
    }

    /**
     * Loads the metadata of the class in question and all it's ancestors whose metadata
     * is still not loaded.
     *
     * Important: The class $name does not necessarily exist at this point here.
     * Scenarios in a code-generation setup might have access to XML/YAML
     * Mapping files without the actual PHP code existing here. That is why the
     * {@see \Doctrine\Persistence\Mapping\ReflectionService} interface
     * should be used for reflection.
     *
     * @param string $name The name of the class for which the metadata should get loaded.
     * @psalm-param class-string $name
     *
     * @return array<int, string>
     * @psalm-return list<string>
     */
    protected function loadMetadata(string $name)
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        $loaded = [];

        $parentClasses   = $this->getParentClasses($name);
        $parentClasses[] = $name;

        // Move down the hierarchy of parent classes, starting from the topmost class
        $parent          = null;
        $rootEntityFound = false;
        $visited         = [];
        $reflService     = $this->getReflectionService();

        foreach ($parentClasses as $className) {
            if (isset($this->loadedMetadata[$className])) {
                $parent = $this->loadedMetadata[$className];

                if ($this->isEntity($parent)) {
                    $rootEntityFound = true;

                    array_unshift($visited, $className);
                }

                continue;
            }

            $class = $this->newClassMetadataInstance($className);
            $this->initializeReflection($class, $reflService);

            $this->doLoadMetadata($class, $parent, $rootEntityFound, $visited);

            $this->loadedMetadata[$className] = $class;

            $parent = $class;

            if ($this->isEntity($class)) {
                $rootEntityFound = true;

                array_unshift($visited, $className);
            }

            $this->wakeupReflection($class, $reflService);

            $loaded[] = $className;
        }

        return $loaded;
    }

    /**
     * Provides a fallback hook for loading metadata when loading failed due to reflection/mapping exceptions
     *
     * Override this method to implement a fallback strategy for failed metadata loading
     *
     * @return ClassMetadata|null
     * @psalm-return CMTemplate|null
     */
    protected function onNotFoundMetadata(string $className)
    {
        return null;
    }

    /**
     * Actually loads the metadata from the underlying metadata.
     *
     * @param string[] $nonSuperclassParents All parent class names that are
     *                                       not marked as mapped superclasses.
     * @psalm-param CMTemplate $class
     * @psalm-param CMTemplate|null $parent
     *
     * @return void
     */
    abstract protected function doLoadMetadata(
        ClassMetadata $class,
        ?ClassMetadata $parent,
        bool $rootEntityFound,
        array $nonSuperclassParents
    );

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @psalm-param class-string<T> $className
     *
     * @return ClassMetadata<T>
     * @psalm-return CMTemplate
     *
     * @template T of object
     */
    abstract protected function newClassMetadataInstance(string $className);

    /**
     * {@inheritDoc}
     */
    public function isTransient(string $className)
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        if (class_exists($className, false) && (new ReflectionClass($className))->isAnonymous()) {
            return false;
        }

        if (! class_exists($className, false) && strpos($className, ':') !== false) {
            throw MappingException::nonExistingClass($className);
        }

        /** @psalm-var class-string $className */
        return $this->getDriver()->isTransient($className);
    }

    /**
     * Sets the reflectionService.
     *
     * @return void
     */
    public function setReflectionService(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * Gets the reflection service associated with this metadata factory.
     *
     * @return ReflectionService
     */
    public function getReflectionService()
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = new RuntimeReflectionService();
        }

        return $this->reflectionService;
    }

    protected function getCacheKey(string $realClassName): string
    {
        return str_replace('\\', '__', $realClassName) . $this->cacheSalt;
    }

    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @psalm-param class-string<Proxy<T>>|class-string<T> $class
     *
     * @psalm-return class-string<T>
     *
     * @template T of object
     */
    private function getRealClass(string $class): string
    {
        if ($this->proxyClassNameResolver === null) {
            $this->createDefaultProxyClassNameResolver();
        }

        assert($this->proxyClassNameResolver !== null);

        return $this->proxyClassNameResolver->resolveClassName($class);
    }

    private function createDefaultProxyClassNameResolver(): void
    {
        $this->proxyClassNameResolver = new class implements ProxyClassNameResolver {
            /**
             * @psalm-param class-string<Proxy<T>>|class-string<T> $className
             *
             * @psalm-return class-string<T>
             *
             * @template T of object
             */
            public function resolveClassName(string $className): string
            {
                $pos = strrpos($className, '\\' . Proxy::MARKER . '\\');

                if ($pos === false) {
                    /** @psalm-var class-string<T> */
                    return $className;
                }

                /** @psalm-var class-string<T> */
                return substr($className, $pos + Proxy::MARKER_LENGTH + 2);
            }
        };
    }
}
