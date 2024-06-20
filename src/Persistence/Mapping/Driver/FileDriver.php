<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;

use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function is_file;
use function str_replace;

/**
 * Base driver for file-based metadata drivers.
 *
 * A file driver operates in a mode where it loads the mapping files of individual
 * classes on demand. This requires the user to adhere to the convention of 1 mapping
 * file per class and the file names of the mapping files must correspond to the full
 * class name, including namespace, with the namespace delimiters '\', replaced by dots '.'.
 *
 * @template T
 */
abstract class FileDriver implements MappingDriver
{
    /** @var FileLocator */
    protected $locator;

    /**
     * @var mixed[]|null
     * @psalm-var array<class-string, T>|null
     */
    protected $classCache;

    /** @var string */
    protected $globalBasename = '';

    /**
     * Initializes a new FileDriver that looks in the given path(s) for mapping
     * documents and operates in the specified operating mode.
     *
     * @param string|array<int, string>|FileLocator $locator A FileLocator or one/multiple paths
     *                                                       where mapping documents can be found.
     */
    public function __construct($locator, ?string $fileExtension = null)
    {
        if ($locator instanceof FileLocator) {
            $this->locator = $locator;
        } else {
            $this->locator = new DefaultFileLocator((array) $locator, $fileExtension);
        }
    }

    /**
     * Sets the global basename.
     *
     * @return void
     */
    public function setGlobalBasename(string $file)
    {
        $this->globalBasename = $file;
    }

    /**
     * Retrieves the global basename.
     *
     * @return string|null
     */
    public function getGlobalBasename()
    {
        return $this->globalBasename;
    }

    /**
     * Gets the element of schema meta data for the class from the mapping file.
     * This will lazily load the mapping file if it is not loaded yet.
     *
     * @psalm-param class-string $className
     *
     * @return T The element of schema meta data.
     *
     * @throws MappingException
     */
    public function getElement(string $className)
    {
        if ($this->classCache === null) {
            $this->initialize();
        }

        if (isset($this->classCache[$className])) {
            return $this->classCache[$className];
        }

        $result = $this->loadMappingFile($this->locator->findMappingFile($className));

        if (! isset($result[$className])) {
            throw MappingException::invalidMappingFile(
                $className,
                str_replace('\\', '.', $className) . $this->locator->getFileExtension()
            );
        }

        $this->classCache[$className] = $result[$className];

        return $result[$className];
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient(string $className)
    {
        if ($this->classCache === null) {
            $this->initialize();
        }

        if (isset($this->classCache[$className])) {
            return false;
        }

        return ! $this->locator->fileExists($className);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        if ($this->classCache === null) {
            $this->initialize();
        }

        if ($this->classCache === []) {
            return $this->locator->getAllClassNames($this->globalBasename);
        }

        /** @psalm-var array<class-string, ClassMetadata<object>> $classCache */
        $classCache = $this->classCache;

        /** @var list<class-string> $keys */
        $keys = array_keys($classCache);

        return array_values(array_unique(array_merge(
            $keys,
            $this->locator->getAllClassNames($this->globalBasename)
        )));
    }

    /**
     * Loads a mapping file with the given name and returns a map
     * from class/entity names to their corresponding file driver elements.
     *
     * @param string $file The mapping file to load.
     *
     * @return mixed[]
     * @psalm-return array<class-string, T>
     */
    abstract protected function loadMappingFile(string $file);

    /**
     * Initializes the class cache from all the global files.
     *
     * Using this feature adds a substantial performance hit to file drivers as
     * more metadata has to be loaded into memory than might actually be
     * necessary. This may not be relevant to scenarios where caching of
     * metadata is in place, however hits very hard in scenarios where no
     * caching is used.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->classCache = [];
        if ($this->globalBasename === '') {
            return;
        }

        foreach ($this->locator->getPaths() as $path) {
            $file = $path . '/' . $this->globalBasename . $this->locator->getFileExtension();
            if (! is_file($file)) {
                continue;
            }

            $this->classCache = array_merge(
                $this->classCache,
                $this->loadMappingFile($file)
            );
        }
    }

    /**
     * Retrieves the locator used to discover mapping files by className.
     *
     * @return FileLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Sets the locator used to discover mapping files by className.
     *
     * @return void
     */
    public function setLocator(FileLocator $locator)
    {
        $this->locator = $locator;
    }
}
