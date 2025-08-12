<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use AppendIterator;
use CallbackFilterIterator;
use Doctrine\Persistence\Mapping\MappingException;
use FilesystemIterator;
use InvalidArgumentException;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;
use SplFileInfo;

use function array_key_exists;
use function array_map;
use function assert;
use function get_debug_type;
use function get_declared_classes;
use function is_dir;
use function is_file;
use function preg_quote;
use function realpath;
use function sprintf;
use function str_starts_with;

/**
 * ClassLocator implementation that uses a list of file names to locate PHP files
 * and extract class names from them.
 *
 * It is compatible with the Symfony Finder component, but does not require it.
 */
final class FileClassLocator implements ClassLocator
{
    /**
     * @param iterable<string> $fileNames An iterable of file names to include.
     *
     * @throws MappingException if any of the files do not exist.
     */
    public function __construct(
        private iterable $fileNames,
    ) {
    }

    /** @return list<class-string> */
    public function getClassNames(): array
    {
        $includedFiles = [];

        foreach ($this->fileNames as $fileName) {
            if (! is_file($fileName)) {
                throw MappingException::fileDoesNotExist($fileName);
            }

            // realpath() can return false if the file is in a phar archive
            // @phpstan-ignore ternary.shortNotAllowed
            $fileName = realpath($fileName) ?: $fileName;
            if (isset($includedFiles[$fileName])) {
                continue;
            }

            $includedFiles[$fileName] = true;
            require_once $fileName;
        }

        $classes = [];
        foreach (get_declared_classes() as $className) {
            $fileName = (new ReflectionClass($className))->getFileName();

            if ($fileName === false || ! array_key_exists($fileName, $includedFiles)) {
                continue;
            }

            $classes[] = $className;
        }

        return $classes;
    }

    /**
     * Creates a FileClassLocator from an array of directories.
     *
     * @param list<string> $directories
     * @param list<string> $excludedDirectories Directories to exclude from the search.
     * @param string       $fileExtension       The file extension to look for (default is '.php').
     *
     * @throws MappingException if any of the directories are not valid.
     */
    public static function createFromDirectories(
        array $directories,
        array $excludedDirectories = [],
        string $fileExtension = '.php',
    ): self {
        $filesIterator = new AppendIterator();

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($directory);
            }

            /** @var Iterator<array-key,SplFileInfo> $iterator */
            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY,
                ),
                sprintf('/%s$/', preg_quote($fileExtension, '/')),
                RegexIterator::MATCH,
            );

            $filesIterator->append($iterator);
        }

        if ($excludedDirectories !== []) {
            // Get
            $excludedDirectories = array_map(
                // @phpstan-ignore ternary.shortNotAllowed
                static fn (string $dir): string => realpath($dir) ?: $dir,
                $excludedDirectories,
            );

            $filesIterator = new CallbackFilterIterator(
                $filesIterator,
                static function (SplFileInfo $file) use ($excludedDirectories): bool {
                    if (str_starts_with($file->getPath(), 'phar:')) {
                        $sourceFile = $file->getPathname();
                    } else {
                        $sourceFile = $file->getRealPath();
                    }

                    foreach ($excludedDirectories as $excludedDirectory) {
                        if (str_starts_with($sourceFile, $excludedDirectory)) {
                            return false;
                        }
                    }

                    return true;
                },
            );
        }

        return self::createFromSplFiles($filesIterator);
    }

    /**
     * Creates a FileClassLocator from an iterable of SplFileInfo objects.
     *
     * This method can be used with a Symfony Finder or any other iterable
     *
     * @param iterable<SplFileInfo> $files
     */
    public static function createFromSplFiles(iterable $files): self
    {
        return new self((static function ($files) {
            foreach ($files as $file) {
                // @phpstan-ignore function.alreadyNarrowedType, instanceof.alwaysTrue
                assert($file instanceof SplFileInfo, new InvalidArgumentException(sprintf('Expected an iterable of SplFileInfo, got %s', get_debug_type($file))));

                // Skip directories or non-file entries
                if (! $file->isFile()) {
                    continue;
                }

                // Files in phar does not have a real path, so we use the pathname
                // @phpstan-ignore ternary.shortNotAllowed
                yield $file->getRealPath() ?: $file->getPathname();
            }
        })($files));
    }
}
