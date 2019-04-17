<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use Doctrine\Common\Cache\Cache;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Proxy;
use ReflectionException;
use function array_reverse;
use function array_unshift;
use function explode;
use function strpos;
use function strrpos;
use function substr;

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
    protected $cacheSalt = '$CLASSMETADATA';

    /** @var Cache|null */
    private $cacheDriver;

    /** @var ClassMetadata[] */
    private $loadedMetadata = [];

    /** @var bool */
    protected $initialized = false;

    /** @var ReflectionService|null */
    private $reflectionService = null;

    /**
     * Sets the cache driver used by the factory to cache ClassMetadata instances.
     */
    public function setCacheDriver(?Cache $cacheDriver = null) : void
    {
        $this->cacheDriver = $cacheDriver;
    }

    /**
     * Gets the cache driver used by the factory to cache ClassMetadata instances.
     */
    public function getCacheDriver() : ?Cache
    {
        return $this->cacheDriver;
    }

    /**
     * Returns an array of all the loaded metadata currently in memory.
     *
     * @return ClassMetadata[]
     */
    public function getLoadedMetadata() : array
    {
        return $this->loadedMetadata;
    }

    /**
     * Forces the factory to load the metadata of all classes known to the underlying
     * mapping driver.
     *
     * @return ClassMetadata[] The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata() : array
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

    /**
     * Lazy initialization of this stuff, especially the metadata driver,
     * since these are not needed at all when a metadata cache is active.
     */
    abstract protected function initialize() : void;

    /**
     * Gets the fully qualified class-name from the namespace alias.
     */
    abstract protected function getFqcnFromAlias(
        string $namespaceAlias,
        string $simpleClassName
    ) : string;

    /**
     * Returns the mapping driver implementation.
     */
    abstract protected function getDriver() : MappingDriver;

    /**
     * Wakes up reflection after ClassMetadata gets unserialized from cache.
     */
    abstract protected function wakeupReflection(
        ClassMetadata $class,
        ReflectionService $reflService
    ) : void;

    /**
     * Initializes Reflection after ClassMetadata was constructed.
     */
    abstract protected function initializeReflection(
        ClassMetadata $class,
        ReflectionService $reflService
    ) : void;

    /**
     * Checks whether the class metadata is an entity.
     *
     * This method should return false for mapped superclasses or embedded classes.
     */
    abstract protected function isEntity(ClassMetadata $class) : bool;

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @throws ReflectionException
     * @throws MappingException
     */
    public function getMetadataFor(string $className) : ClassMetadata
    {
        if (isset($this->loadedMetadata[$className])) {
            return $this->loadedMetadata[$className];
        }

        // Check for namespace alias
        if (strpos($className, ':') !== false) {
            [$namespaceAlias, $simpleClassName] = explode(':', $className, 2);

            $realClassName = $this->getFqcnFromAlias($namespaceAlias, $simpleClassName);
        } else {
            $realClassName = $this->getRealClass($className);
        }

        if (isset($this->loadedMetadata[$realClassName])) {
            // We do not have the alias name in the map, include it
            return $this->loadedMetadata[$className] = $this->loadedMetadata[$realClassName];
        }

        $loadingException = null;

        try {
            if ($this->cacheDriver !== null) {
                $cached = $this->cacheDriver->fetch($realClassName . $this->cacheSalt);

                if ($cached instanceof ClassMetadata) {
                    $this->loadedMetadata[$realClassName] = $cached;

                    $this->wakeupReflection($cached, $this->getReflectionService());
                } else {
                    foreach ($this->loadMetadata($realClassName) as $loadedClassName) {
                        $this->cacheDriver->save(
                            $loadedClassName . $this->cacheSalt,
                            $this->loadedMetadata[$loadedClassName]
                        );
                    }
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
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor(string $className) : bool
    {
        return isset($this->loadedMetadata[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * NOTE: This is only useful in very special cases, like when generating proxy classes.
     */
    public function setMetadataFor(string $className, ClassMetadata $class) : void
    {
        $this->loadedMetadata[$className] = $class;
    }

    /**
     * Gets an array of parent classes for the given entity class.
     *
     * @return string[]
     */
    protected function getParentClasses(string $name) : array
    {
        // Collect parent classes, ignoring transient (not-mapped) classes.
        $parentClasses = [];

        $parentClasses = $this->getReflectionService()
            ->getParentClasses($name);

        foreach (array_reverse($parentClasses) as $parentClass) {
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
     * {@see Doctrine\Persistence\Mapping\ReflectionService} interface
     * should be used for reflection.
     *
     * @param string $name The name of the class for which the metadata should get loaded.
     *
     * @return string[]
     */
    protected function loadMetadata(string $name) : array
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
     */
    protected function onNotFoundMetadata(string $className) : ?ClassMetadata
    {
        return null;
    }

    /**
     * Actually loads the metadata from the underlying metadata.
     *
     * @param string[] $nonSuperclassParents All parent class names
     *                                       that are not marked as mapped superclasses.
     */
    abstract protected function doLoadMetadata(
        ClassMetadata $class,
        ?ClassMetadata $parent,
        bool $rootEntityFound,
        array $nonSuperclassParents
    ) : void;

    /**
     * Creates a new ClassMetadata instance for the given class name.
     */
    abstract protected function newClassMetadataInstance(string $className) : ClassMetadata;

    /**
     * {@inheritDoc}
     */
    public function isTransient(string $class) : bool
    {
        if (! $this->initialized) {
            $this->initialize();
        }

        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            [$namespaceAlias, $simpleClassName] = explode(':', $class, 2);

            $class = $this->getFqcnFromAlias($namespaceAlias, $simpleClassName);
        }

        return $this->getDriver()->isTransient($class);
    }

    /**
     * Sets the reflectionService.
     */
    public function setReflectionService(ReflectionService $reflectionService) : void
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * Gets the reflection service associated with this metadata factory.
     */
    public function getReflectionService() : ReflectionService
    {
        if ($this->reflectionService === null) {
            $this->reflectionService = new RuntimeReflectionService();
        }

        return $this->reflectionService;
    }

    /**
     * Gets the real class name of a class name that could be a proxy.
     */
    private function getRealClass(string $class) : string
    {
        $pos = strrpos($class, '\\' . Proxy::MARKER . '\\');

        if ($pos === false) {
            return $class;
        }

        return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
    }
}
