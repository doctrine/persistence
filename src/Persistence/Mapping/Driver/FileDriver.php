<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;

use function array_keys;
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
    protected FileLocator $locator;

    /**
     * @var mixed[]|null
     * @phpstan-var array<class-string, T>|null
     */
    protected array|null $classCache = null;
    protected string $globalBasename = '';

    /**
     * Initializes a new FileDriver that looks in the given path(s) for mapping
     * documents and operates in the specified operating mode.
     *
     * @param string|array<int, string>|FileLocator $locator A FileLocator or one/multiple paths
     *                                                       where mapping documents can be found.
     */
    public function __construct(string|array|FileLocator $locator, string|null $fileExtension = null)
    {
        if ($locator instanceof FileLocator) {
            $this->locator = $locator;
        } else {
            $this->locator = new DefaultFileLocator((array) $locator, $fileExtension);
        }
    }

    /** Sets the global basename. */
    public function setGlobalBasename(string $file): void
    {
        $this->globalBasename = $file;
    }

    /** Retrieves the global basename. */
    public function getGlobalBasename(): string
    {
        return $this->globalBasename;
    }

    /**
     * Gets the element of schema meta data for the class from the mapping file.
     * This will lazily load the mapping file if it is not loaded yet.
     *
     * @phpstan-param class-string $className
     *
     * @return T The element of schema meta data.
     *
     * @throws MappingException
     */
    public function getElement(string $className): mixed
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
                str_replace('\\', '.', $className) . $this->locator->getFileExtension(),
            );
        }

        $this->classCache[$className] = $result[$className];

        return $result[$className];
    }

    public function isTransient(string $className): bool
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
    public function getAllClassNames(): array
    {
        if ($this->classCache === null) {
            $this->initialize();
        }

        if ($this->classCache === []) {
            return $this->locator->getAllClassNames($this->globalBasename);
        }

        /** @phpstan-var non-empty-array<class-string, T> $classCache */
        $classCache = $this->classCache;

        /** @var list<class-string> $keys */
        $keys = array_keys($classCache);

        return array_values(array_unique([...$keys, ...$this->locator->getAllClassNames($this->globalBasename)]));
    }

    /**
     * Loads a mapping file with the given name and returns a map
     * from class/entity names to their corresponding file driver elements.
     *
     * @param string $file The mapping file to load.
     *
     * @return mixed[]
     * @phpstan-return array<class-string, T>
     */
    abstract protected function loadMappingFile(string $file): array;

    /**
     * Initializes the class cache from all the global files.
     *
     * Using this feature adds a substantial performance hit to file drivers as
     * more metadata has to be loaded into memory than might actually be
     * necessary. This may not be relevant to scenarios where caching of
     * metadata is in place, however hits very hard in scenarios where no
     * caching is used.
     */
    protected function initialize(): void
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

            $this->classCache = [...$this->classCache, ...$this->loadMappingFile($file)];
        }
    }

    /** Retrieves the locator used to discover mapping files by className. */
    public function getLocator(): FileLocator
    {
        return $this->locator;
    }

    /** Sets the locator used to discover mapping files by className. */
    public function setLocator(FileLocator $locator): void
    {
        $this->locator = $locator;
    }
}
