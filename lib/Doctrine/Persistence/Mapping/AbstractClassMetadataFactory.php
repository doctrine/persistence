<?php

namespace Doctrine\Persistence\Mapping;

use BadMethodCallException;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Proxy;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionException;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Symfony\Component\Cache\DoctrineProvider;

use function array_combine;
use function array_keys;
use function array_map;
use function array_reverse;
use function array_unshift;
use function assert;
use function explode;
use function sprintf;
use function str_replace;
use function strpos;
use function strrpos;
use function substr;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping informations of a class which describes how a class should be mapped
 * to a relational database.
 *
 * This class was abstracted from the ORM ClassMetadataFactory.
 */
abstract class AbstractClassMetadataFactory implements ClassMetadataFactory
{
    /**
     * Salt used by specific Object Manager implementation.
     *
     * @var string
     */
    protected $cacheSalt = '__CLASSMETADATA__';

    /** @var Cache|null */
    private $cacheDriver;

    /** @var CacheItemPoolInterface|null */
    private $cache;

    /** @var ClassMetadata[] */
    private $loadedMetadata = [];

    /** @var bool */
    protected $initialized = false;

    /** @var ReflectionService|null */
    private $reflectionService = null;

    /** @var ProxyClassNameResolver|null */
    private $proxyClassNameResolver = null;

    /**
     * Sets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @deprecated setCacheDriver was deprecated in doctrine/persistence 2.2 and will be removed in 3.0. Use setCache instead
     *
     * @return void
     */
    public function setCacheDriver(?Cache $cacheDriver = null)
    {
        @trigger_error(sprintf('%s is deprecated. Use setCache() with a PSR-6 cache instead.', __METHOD__), E_USER_DEPRECATED);

        $this->cacheDriver = $cacheDriver;

        if ($cacheDriver === null) {
            $this->cache = null;

            return;
        }

        if (! $cacheDriver instanceof CacheProvider) {
            throw new BadMethodCallException('Cannot convert cache to PSR-6 cache');
        }

        $this->cache = new DoctrineAdapter($cacheDriver);
    }

    /**
     * Gets the cache driver used by the factory to cache ClassMetadata instances.
     *
     * @deprecated getCacheDriver was deprecated in doctrine/persistence 2.2 and will be removed in 3.0. Use getCache instead
     *
     * @return Cache|null
     */
    public function getCacheDriver()
    {
        @trigger_error(sprintf('%s is deprecated. Use getCache() instead.', __METHOD__), E_USER_DEPRECATED);

        return $this->cacheDriver;
    }

    public function setCache(CacheItemPoolInterface $cache): void
    {
        $this->cache       = $cache;
        $this->cacheDriver = new DoctrineProvider($cache);
    }

    public function getCache(): ?CacheItemPoolInterface
    {
        return $this->cache;
    }

    /**
     * Returns an array of all the loaded metadata currently in memory.
     *
     * @return ClassMetadata[]
     */
    public function getLoadedMetadata()
    {
        return $this->loadedMetadata;
    }

    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return ClassMetadata[] The ClassMetadata instances of all mapped classes.
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
     * Gets the fully qualified class-name from the namespace alias.
     *
     * @param string $namespaceAlias
     * @param string $simpleClassName
     *
     * @return string
     *
     * @psalm-return class-string
     */
    abstract protected function getFqcnFromAlias($namespaceAlias, $simpleClassName);

    /**
     * Returns the mapping driver implementation.
     *
     * @return MappingDriver
     */
    abstract protected function getDriver();

    /**
     * Wakes up reflection after ClassMetadata gets unserialized from cache.
     *
     * @return void
     */
    abstract protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService);

    /**
     * Initializes Reflection after ClassMetadata was constructed.
     *
     * @return void
     */
    abstract protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService);

    /**
     * Checks whether the class metadata is an entity.
     *
     * This method should return false for mapped superclasses or embedded classes.
     *
     * @return bool
     */
    abstract protected function isEntity(ClassMetadata $class);

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @return ClassMetadata
     *
     * @throws ReflectionException
     * @throws MappingException
     *
     * @psalm-param class-string|string $className
     */
    public function getMetadataFor($className)
    {
        if (isset($this->loadedMetadata[$className])) {
            return $this->loadedMetadata[$className];
        }

        // Check for namespace alias
        if (strpos($className, ':') !== false) {
            [$namespaceAlias, $simpleClassName] = explode(':', $className, 2);

            $realClassName = $this->getFqcnFromAlias($namespaceAlias, $simpleClassName);
        } else {
            /** @psalm-var class-string $className */
            $realClassName = $this->getRealClass($className);
        }

        if (isset($this->loadedMetadata[$realClassName])) {
            // We do not have the alias name in the map, include it
            return $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];
        }

        $loadingException = null;

        try {
            if ($this->cache) {
                $cached = $this->cache->getItem($this->getCacheKey($realClassName))->get();
                if ($cached instanceof ClassMetadata) {
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

            if (! $fallbackMetadataResponse) {
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
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param string $className
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     *
     * @param string        $className
     * @param ClassMetadata $class
     *
     * @return void
     */
    public function setMetadataFor($className, $class)
    {
        $this->loadedMetadata[$className] = $class;
    }

    /**
     * Gets an array of parent classes for the given entity class.
     *
     * @param string $name
     *
     * @return string[]
     *
     * @psalm-param class-string $name
     */
    protected function getParentClasses($name)
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
     * {@see Doctrine\Common\Persistence\Mapping\ReflectionService} interface
     * should be used for reflection.
     *
     * @param string $name The name of the class for which the metadata should get loaded.
     *
     * @return string[]
     *
     * @psalm-param class-string $name
     */
    protected function loadMetadata($name)
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
     * @param string $className
     *
     * @return ClassMetadata|null
     */
    protected function onNotFoundMetadata($className)
    {
        return null;
    }

    /**
     * Actually loads the metadata from the underlying metadata.
     *
     * @param ClassMetadata      $class
     * @param ClassMetadata|null $parent
     * @param bool               $rootEntityFound
     * @param string[]           $nonSuperclassParents All parent class names
     *                                                 that are not marked as mapped superclasses.
     *
     * @return void
     */
    abstract protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents);

    /**
     * Creates a new ClassMetadata instance for the given class name.
     *
     * @param string $className
     *
     * @return ClassMetadata
     */
    abstract protected function newClassMetadataInstance($className);

    /**
     * {@inheritDoc}
     *
     * @psalm-param class-string|string $class
     */
    public function isTransient($class)
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            [$namespaceAlias, $simpleClassName] = explode(':', $class, 2);
            $class                              = $this->getFqcnFromAlias($namespaceAlias, $simpleClassName);
        }

        /** @psalm-var class-string $class */
        return $this->getDriver()->isTransient($class);
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
     * @template T of object
     * @psalm-param class-string<Proxy<T>>|class-string<T> $class
     * @psalm-return class-string<T>
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
             * @template T of object
             * @psalm-param class-string<Proxy<T>>|class-string<T> $className
             * @psalm-return class-string<T>
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
