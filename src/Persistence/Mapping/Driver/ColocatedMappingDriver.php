<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

use function array_merge;
use function array_unique;
use function assert;
use function get_declared_classes;
use function in_array;
use function is_dir;
use function preg_match;
use function preg_quote;
use function realpath;
use function str_replace;
use function strpos;

/**
 * The ColocatedMappingDriver reads the mapping metadata located near the code.
 */
trait ColocatedMappingDriver
{
    /**
     * The paths where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected $paths = [];

    /**
     * The paths excluded from path where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected $excludePaths = [];

    /**
     * The file extension of mapping documents.
     *
     * @var string
     */
    protected $fileExtension = '.php';

    /**
     * Cache for getAllClassNames().
     *
     * @var array<int, string>|null
     * @psalm-var list<class-string>|null
     */
    protected $classNames;

    /**
     * Appends lookup paths to metadata driver.
     *
     * @param array<int, string> $paths
     *
     * @return void
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_unique(array_merge($this->paths, $paths));
    }

    /**
     * Retrieves the defined metadata lookup paths.
     *
     * @return array<int, string>
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Append exclude lookup paths to metadata driver.
     *
     * @param string[] $paths
     *
     * @return void
     */
    public function addExcludePaths(array $paths)
    {
        $this->excludePaths = array_unique(array_merge($this->excludePaths, $paths));
    }

    /**
     * Retrieve the defined metadata lookup exclude paths.
     *
     * @return array<int, string>
     */
    public function getExcludePaths()
    {
        return $this->excludePaths;
    }

    /**
     * Gets the file extension used to look for mapping files under.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * Sets the file extension used to look for mapping files under.
     *
     * @return void
     */
    public function setFileExtension(string $fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * {@inheritDoc}
     *
     * Returns whether the class with the specified name is transient. Only non-transient
     * classes, that is entities and mapped superclasses, should have their metadata loaded.
     *
     * @param string $className
     * @psalm-param class-string $className
     *
     * @return bool
     */
    abstract public function isTransient($className);

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return string[] The names of all mapped classes known to this driver.
     * @psalm-return list<class-string>
     */
    public function getAllClassNames()
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if ($this->paths === []) {
            throw MappingException::pathRequiredForDriver(static::class);
        }

        $classes       = [];
        $includedFiles = [];

        foreach ($this->paths as $path) {
            if (! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+' . preg_quote($this->fileExtension) . '$/i',
                RecursiveRegexIterator::GET_MATCH
            );

            foreach ($iterator as $file) {
                $sourceFile = $file[0];

                if (preg_match('(^phar:)i', $sourceFile) === 0) {
                    $sourceFile = realpath($sourceFile);
                }

                foreach ($this->excludePaths as $excludePath) {
                    $realExcludePath = realpath($excludePath);
                    assert($realExcludePath !== false);
                    $exclude = str_replace('\\', '/', $realExcludePath);
                    $current = str_replace('\\', '/', $sourceFile);

                    if (strpos($current, $exclude) !== false) {
                        continue 2;
                    }
                }

                require_once $sourceFile;

                $includedFiles[] = $sourceFile;
            }
        }

        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc = new ReflectionClass($className);

            $sourceFile = $rc->getFileName();

            if (! in_array($sourceFile, $includedFiles, true) || $this->isTransient($className)) {
                continue;
            }

            $classes[] = $className;
        }

        $this->classNames = $classes;

        return $classes;
    }
}
