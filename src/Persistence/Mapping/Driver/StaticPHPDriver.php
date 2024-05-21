<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\MappingException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

use function array_merge;
use function array_unique;
use function get_declared_classes;
use function in_array;
use function is_dir;
use function method_exists;
use function realpath;

/**
 * The StaticPHPDriver calls a static loadMetadata() method on your entity
 * classes where you can manually populate the ClassMetadata instance.
 */
class StaticPHPDriver implements MappingDriver
{
    /**
     * Paths of entity directories.
     *
     * @var array<int, string>
     */
    private $paths = [];

    /**
     * Map of all class names.
     *
     * @var array<int, string>
     * @psalm-var list<class-string>
     */
    private $classNames;

    /** @param array<int, string>|string $paths */
    public function __construct($paths)
    {
        $this->addPaths((array) $paths);
    }

    /**
     * @param array<int, string> $paths
     *
     * @return void
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_unique(array_merge($this->paths, $paths));
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(string $className, ClassMetadata $metadata)
    {
        $className::loadMetadata($metadata);
    }

    /**
     * {@inheritDoc}
     *
     * @todo Same code exists in ColocatedMappingDriver, should we re-use it
     * somehow or not worry about it?
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

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if ($file->getBasename('.php') === $file->getBasename()) {
                    continue;
                }

                $sourceFile = realpath($file->getPathName());
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

    /**
     * {@inheritDoc}
     */
    public function isTransient(string $className)
    {
        return ! method_exists($className, 'loadMetadata');
    }
}
