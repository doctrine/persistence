<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function array_unique;
use function assert;
use function is_dir;
use function is_file;
use function is_string;
use function str_replace;

use const DIRECTORY_SEPARATOR;

/**
 * Locates the file that contains the metadata information for a given class name.
 *
 * This behavior is independent of the actual content of the file. It just detects
 * the file which is responsible for the given class name.
 */
class DefaultFileLocator implements FileLocator
{
    /**
     * The paths where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected array $paths = [];

    /** The file extension of mapping documents. */
    protected string|null $fileExtension;

    /**
     * Initializes a new FileDriver that looks in the given path(s) for mapping
     * documents and operates in the specified operating mode.
     *
     * @param string|array<int, string> $paths         One or multiple paths where mapping documents
     *                                                 can be found.
     * @param string|null               $fileExtension The file extension of mapping documents,
     *                                                 usually prefixed with a dot.
     */
    public function __construct(string|array $paths, string|null $fileExtension = null)
    {
        $this->addPaths((array) $paths);
        $this->fileExtension = $fileExtension;
    }

    /**
     * Appends lookup paths to metadata driver.
     *
     * @param array<int, string> $paths
     */
    public function addPaths(array $paths): void
    {
        $this->paths = array_unique([...$this->paths, ...$paths]);
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

    /** Gets the file extension used to look for mapping files under. */
    public function getFileExtension(): string|null
    {
        return $this->fileExtension;
    }

    /**
     * Sets the file extension used to look for mapping files under.
     *
     * @param string|null $fileExtension The file extension to set.
     */
    public function setFileExtension(string|null $fileExtension): void
    {
        $this->fileExtension = $fileExtension;
    }

    public function findMappingFile(string $className): string
    {
        $fileName = str_replace('\\', '.', $className) . $this->fileExtension;

        // Check whether file exists
        foreach ($this->paths as $path) {
            if (is_file($path . DIRECTORY_SEPARATOR . $fileName)) {
                return $path . DIRECTORY_SEPARATOR . $fileName;
            }
        }

        throw MappingException::mappingFileNotFound($className, $fileName);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames(string $globalBasename): array
    {
        if ($this->paths === []) {
            return [];
        }

        $classes = [];

        foreach ($this->paths as $path) {
            if (! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::LEAVES_ONLY,
            );

            foreach ($iterator as $file) {
                $fileName = $file->getBasename($this->fileExtension);

                if ($fileName === $file->getBasename() || $fileName === $globalBasename) {
                    continue;
                }

                // NOTE: All files found here means classes are not transient!

                assert(is_string($fileName));
                /** @phpstan-var class-string */
                $class     = str_replace('.', '\\', $fileName);
                $classes[] = $class;
            }
        }

        return $classes;
    }

    public function fileExists(string $className): bool
    {
        $fileName = str_replace('\\', '.', $className) . $this->fileExtension;

        // Check whether file exists
        foreach ($this->paths as $path) {
            if (is_file($path . DIRECTORY_SEPARATOR . $fileName)) {
                return true;
            }
        }

        return false;
    }
}
