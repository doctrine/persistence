<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use Exception;

use function implode;
use function sprintf;

/**
 * A MappingException indicates that something is wrong with the mapping setup.
 */
class MappingException extends Exception
{
    /** @param array<int, string> $namespaces */
    public static function classNotFoundInNamespaces(
        string $className,
        array $namespaces,
    ): self {
        return new self(sprintf(
            "The class '%s' was not found in the chain configured namespaces %s",
            $className,
            implode(', ', $namespaces),
        ));
    }

    /** @param class-string $driverClassName */
    public static function pathRequiredForDriver(string $driverClassName): self
    {
        return new self(sprintf(
            'Specifying the paths to your entities is required when using %s to retrieve all class names.',
            $driverClassName,
        ));
    }

    public static function fileMappingDriversRequireConfiguredDirectoryPath(
        string|null $path = null,
    ): self {
        if ($path !== null) {
            $path = '[' . $path . ']';
        }

        return new self(sprintf(
            'File mapping drivers must have a valid directory path, ' .
            'however the given path %s seems to be incorrect!',
            (string) $path,
        ));
    }

    public static function mappingFileNotFound(string $entityName, string $fileName): self
    {
        return new self(sprintf(
            "No mapping file found named '%s' for class '%s'.",
            $fileName,
            $entityName,
        ));
    }

    public static function invalidMappingFile(string $entityName, string $fileName): self
    {
        return new self(sprintf(
            "Invalid mapping file '%s' for class '%s'.",
            $fileName,
            $entityName,
        ));
    }

    public static function nonExistingClass(string $className): self
    {
        return new self(sprintf("Class '%s' does not exist", $className));
    }

    /** @param class-string $className */
    public static function classIsAnonymous(string $className): self
    {
        return new self(sprintf('Class "%s" is anonymous', $className));
    }
}
