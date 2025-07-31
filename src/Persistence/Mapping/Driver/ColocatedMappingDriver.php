<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;
use ReflectionClass;

use function array_merge;
use function array_unique;
use function assert;
use function get_declared_classes;
use function preg_match;
use function realpath;
use function str_contains;
use function str_replace;

/**
 * The ColocatedMappingDriver reads the mapping metadata located near the code.
 */
trait ColocatedMappingDriver
{
    /** @var iterable<array-key,string> */
    private iterable $filePaths;

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

        if ($this->paths === [] && ! isset($this->filePaths)) {
            throw MappingException::pathRequiredForDriver(static::class);
        }

        $dirFilesIterator = new DirectoryFilesIterator($this->paths, $this->fileExtension);

        /** @var iterable<string> $filePathsIterator */
        $filePathsIterator = $this->concatIterables(
            $this->filePaths ?? [],
            new FilePathNameIterator($dirFilesIterator),
        );

        /** @var array<string,true> $includedFiles */
        $includedFiles = [];

        foreach ($filePathsIterator as $sourceFile) {
            if (preg_match('(^phar:)i', $sourceFile) === 0) {
                $sourceFile = realpath($sourceFile);
                assert($sourceFile !== false);
            }

            foreach ($this->excludePaths as $excludePath) {
                $realExcludePath = realpath($excludePath);
                assert($realExcludePath !== false);
                $exclude = str_replace('\\', '/', $realExcludePath);
                $current = str_replace('\\', '/', $sourceFile);

                if (str_contains($current, $exclude)) {
                    continue 2;
                }
            }

            require_once $sourceFile;

            $includedFiles[$sourceFile] = true;
        }

        $classes  = [];
        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc = new ReflectionClass($className);

            $sourceFile = $rc->getFileName();

            if (! isset($includedFiles[$sourceFile]) || $this->isTransient($className)) {
                continue;
            }

            $classes[] = $className;
        }

        $this->classNames = $classes;

        return $classes;
    }

    /**
     * @internal
     *
     * @param iterable<TKey, T> $iterable1
     * @param iterable<TKey, T> $iterable2
     *
     * @return iterable<TKey, T>
     *
     * @template TKey
     * @template T
     */
    private function concatIterables(iterable $iterable1, iterable $iterable2): iterable
    {
        yield from $iterable1;
        yield from $iterable2;
    }
}
