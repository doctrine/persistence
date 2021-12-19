<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function array_keys;
use function array_merge;
use function assert;
use function is_dir;
use function is_file;
use function is_int;
use function realpath;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strrpos;
use function strtr;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * The Symfony File Locator makes a simplifying assumptions compared
 * to the DefaultFileLocator. By assuming paths only contain entities of a certain
 * namespace the mapping files consists of the short classname only.
 */
class SymfonyFileLocator implements FileLocator
{
    /**
     * The paths where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected $paths = [];

    /**
     * A map of mapping directory path to namespace prefix used to expand class shortnames.
     *
     * @var array<string, string>
     */
    protected $prefixes = [];

    /**
     * File extension that is searched for.
     *
     * @var string|null
     */
    protected $fileExtension;

    /**
     * Represents PHP namespace delimiters when looking for files
     *
     * @var string
     */
    private $nsSeparator;

    /**
     * @param array<string, string> $prefixes
     * @param string                $nsSeparator String which would be used when converting FQCN
     *                                           to filename and vice versa. Should not be empty
     */
    public function __construct(
        array $prefixes,
        string $fileExtension = '',
        string $nsSeparator = '.'
    ) {
        $this->addNamespacePrefixes($prefixes);
        $this->fileExtension = $fileExtension;

        if ($nsSeparator === '') {
            throw new InvalidArgumentException('Namespace separator should not be empty');
        }

        $this->nsSeparator = $nsSeparator;
    }

    /**
     * Adds Namespace Prefixes.
     *
     * @param array<string, string> $prefixes
     *
     * @return void
     */
    public function addNamespacePrefixes(array $prefixes)
    {
        $this->prefixes = array_merge($this->prefixes, $prefixes);
        $this->paths    = array_merge($this->paths, array_keys($prefixes));
    }

    /**
     * Gets Namespace Prefixes.
     *
     * @return string[]
     */
    public function getNamespacePrefixes()
    {
        return $this->prefixes;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * {@inheritDoc}
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * Sets the file extension used to look for mapping files under.
     *
     * @param string $fileExtension The file extension to set.
     *
     * @return void
     */
    public function setFileExtension(string $fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function fileExists(string $className)
    {
        $defaultFileName = str_replace('\\', $this->nsSeparator, $className) . $this->fileExtension;
        foreach ($this->paths as $path) {
            if (! isset($this->prefixes[$path])) {
                // global namespace class
                if (is_file($path . DIRECTORY_SEPARATOR . $defaultFileName)) {
                    return true;
                }

                continue;
            }

            $prefix = $this->prefixes[$path];

            if (strpos($className, $prefix . '\\') !== 0) {
                continue;
            }

            $filename = $path . '/' . strtr(substr($className, strlen($prefix) + 1), '\\', $this->nsSeparator) . $this->fileExtension;

            if (is_file($filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames(?string $globalBasename = null)
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
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                $fileName = $file->getBasename($this->fileExtension);

                if ($fileName === $file->getBasename() || $fileName === $globalBasename) {
                    continue;
                }

                // NOTE: All files found here means classes are not transient!
                if (isset($this->prefixes[$path])) {
                    // Calculate namespace suffix for given prefix as a relative path from basepath to file path
                    $nsSuffix = strtr(
                        substr($this->realpath($file->getPath()), strlen($this->realpath($path))),
                        $this->nsSeparator,
                        '\\'
                    );

                    /** @psalm-var class-string */
                    $class = $this->prefixes[$path] . str_replace(DIRECTORY_SEPARATOR, '\\', $nsSuffix) . '\\' . str_replace($this->nsSeparator, '\\', $fileName);
                } else {
                    /** @psalm-var class-string */
                    $class = str_replace($this->nsSeparator, '\\', $fileName);
                }

                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * {@inheritDoc}
     */
    public function findMappingFile(string $className)
    {
        $defaultFileName = str_replace('\\', $this->nsSeparator, $className) . $this->fileExtension;
        foreach ($this->paths as $path) {
            if (! isset($this->prefixes[$path])) {
                if (is_file($path . DIRECTORY_SEPARATOR . $defaultFileName)) {
                    return $path . DIRECTORY_SEPARATOR . $defaultFileName;
                }

                continue;
            }

            $prefix = $this->prefixes[$path];

            if (strpos($className, $prefix . '\\') !== 0) {
                continue;
            }

            $filename = $path . '/' . strtr(substr($className, strlen($prefix) + 1), '\\', $this->nsSeparator) . $this->fileExtension;
            if (is_file($filename)) {
                return $filename;
            }
        }

        $pos = strrpos($className, '\\');
        assert(is_int($pos));

        throw MappingException::mappingFileNotFound(
            $className,
            substr($className, $pos + 1) . $this->fileExtension
        );
    }

    private function realpath(string $path): string
    {
        $realpath = realpath($path);

        if ($realpath === false) {
            throw new RuntimeException(sprintf('Could not get realpath for %s', $path));
        }

        return $realpath;
    }
}
