<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;

use function array_filter;
use function array_merge;
use function array_unique;
use function array_values;

/**
 * The ColocatedMappingDriver reads the mapping metadata located near the code.
 */
trait ColocatedMappingDriver
{
    private ClassLocator|null $classLocator = null;

    /**
     * The directory paths where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected array $paths = [];

    /**
     * The paths excluded from path where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected array $excludePaths = [];

    /** The file extension of mapping documents. */
    protected string $fileExtension = '.php';

    /**
     * Cache for {@see getAllClassNames()}.
     *
     * @var array<int, string>|null
     * @phpstan-var list<class-string>|null
     */
    protected array|null $classNames = null;

    /**
     * Appends lookup paths to metadata driver.
     *
     * @param array<int, string> $paths
     */
    public function addPaths(array $paths): void
    {
        $this->paths = array_unique(array_merge($this->paths, $paths));
    }

    /**
     * Retrieves the defined metadata lookup paths.
     *
     * @return array<int, string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Append exclude lookup paths to a metadata driver.
     *
     * @param string[] $paths
     */
    public function addExcludePaths(array $paths): void
    {
        $this->excludePaths = array_unique(array_merge($this->excludePaths, $paths));
    }

    /**
     * Retrieve the defined metadata lookup exclude paths.
     *
     * @return array<int, string>
     */
    public function getExcludePaths(): array
    {
        return $this->excludePaths;
    }

    /** Gets the file extension used to look for mapping files under. */
    public function getFileExtension(): string
    {
        return $this->fileExtension;
    }

    /** Sets the file extension used to look for mapping files under. */
    public function setFileExtension(string $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * {@inheritDoc}
     *
     * Returns whether the class with the specified name is transient. Only non-transient
     * classes, that is entities and mapped superclasses, should have their metadata loaded.
     *
     * @phpstan-param class-string $className
     */
    abstract public function isTransient(string $className): bool;

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return string[] The names of all mapped classes known to this driver.
     * @phpstan-return list<class-string>
     */
    public function getAllClassNames(): array
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if ($this->paths === [] && $this->classLocator === null) {
            throw MappingException::pathRequiredForDriver(static::class);
        }

        $classNames = $this->classLocator?->getClassNames() ?? [];

        if ($this->paths !== []) {
            $classNames = array_unique([
                ...FileClassLocator::createFromDirectories($this->paths, $this->excludePaths, $this->fileExtension)->getClassNames(),
                ...$classNames,
            ]);
        }

        return $this->classNames = array_values(array_filter(
            $classNames,
            fn (string $className): bool => ! $this->isTransient($className),
        ));
    }
}
