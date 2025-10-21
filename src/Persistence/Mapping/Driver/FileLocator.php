<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

/**
 * Locates the file that contains the metadata information for a given class name.
 *
 * This behavior is independent of the actual content of the file. It just detects
 * the file which is responsible for the given class name.
 */
interface FileLocator
{
    /** Locates mapping file for the given class name. */
    public function findMappingFile(string $className): string;

    /**
     * Gets all class names that are found with this file locator.
     *
     * @param string $globalBasename Passed to allow excluding the basename.
     *
     * @return array<int, string>
     * @phpstan-return list<class-string>
     */
    public function getAllClassNames(string $globalBasename): array;

    /** Checks if a file can be found for this class name. */
    public function fileExists(string $className): bool;

    /**
     * Gets all the paths that this file locator looks for mapping files.
     *
     * @return array<int, string>
     */
    public function getPaths(): array;

    /** Gets the file extension that mapping files are suffixed with. */
    public function getFileExtension(): string|null;
}
