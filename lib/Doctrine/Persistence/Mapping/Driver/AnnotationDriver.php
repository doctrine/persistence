<?php

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Deprecations\Deprecation;
use Doctrine\Persistence\Mapping\MappingException;
use FilesystemIterator;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;

use function array_merge;
use function array_unique;
use function assert;
use function func_get_arg;
use function func_num_args;
use function get_class;
use function get_declared_classes;
use function in_array;
use function is_dir;
use function is_object;
use function preg_match;
use function preg_quote;
use function realpath;
use function sprintf;
use function str_replace;
use function strpos;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 */
abstract class AnnotationDriver implements MappingDriver
{
    /**
     * The annotation reader.
     *
     * @deprecated Store the reader inside the child class instead.
     *
     * @var Reader|null
     */
    protected $reader;

    /**
     * The paths where to look for mapping files.
     *
     * @var string[]
     */
    protected $paths = [];

    /**
     * The paths excluded from path where to look for mapping files.
     *
     * @var string[]
     */
    protected $excludePaths = [];

    /**
     * The file extension of mapping documents.
     *
     * @var string
     */
    protected $fileExtension = '.php';

    /**
     * Cache for AnnotationDriver#getAllClassNames().
     *
     * @var string[]|null
     * @psalm-var list<class-string>|null
     */
    protected $classNames;

    /**
     * Name of the entity annotations as keys.
     *
     * @deprecated
     *
     * @var array<class-string, bool|int>
     */
    protected $entityAnnotationClasses = [];

    /**
     * Initializes a new AnnotationDriver that uses the given AnnotationReader for reading
     * docblock annotations.
     *
     * @param string|string[]|null $paths One or multiple paths where mapping classes can be found.
     */
    public function __construct($paths = null)
    {
        if (is_object($paths)) {
            Deprecation::trigger(
                'doctrine/persistence',
                'https://github.com/doctrine/persistence/pull/217',
                'Passing an annotation reader to %s is deprecated. Store the reader in the child class instead and override isTransient().',
                __METHOD__
            );
            $this->reader = $paths;
            $paths        = func_num_args() > 1 ? func_get_arg(1) : null;
        }

        if (! $paths) {
            return;
        }

        $this->addPaths((array) $paths);
    }

    /**
     * Appends lookup paths to metadata driver.
     *
     * @param string[] $paths
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
     * @return string[]
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
     * @return string[]
     */
    public function getExcludePaths()
    {
        return $this->excludePaths;
    }

    /**
     * Retrieve the current annotation reader
     *
     * @deprecated
     *
     * @return Reader
     */
    public function getReader()
    {
        Deprecation::trigger(
            'doctrine/persistence',
            'https://github.com/doctrine/persistence/pull/217',
            '%s is deprecated without replacement.',
            __METHOD__
        );

        if ($this->reader === null) {
            throw new LogicException('The reader has not been initialized.');
        }

        return $this->reader;
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
     * @param string $fileExtension The file extension to set.
     *
     * @return void
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Returns whether the class with the specified name is transient. Only non-transient
     * classes, that is entities and mapped superclasses, should have their metadata loaded.
     *
     * A class is non-transient if it is annotated with an annotation
     * from the {@see AnnotationDriver::entityAnnotationClasses}.
     *
     * {@inheritDoc}
     */
    public function isTransient($className)
    {
        Deprecation::trigger(
            'doctrine/persistence',
            'https://github.com/doctrine/persistence/pull/217',
            'Not overriding %s in %s is deprecated.',
            __METHOD__,
            static::class
        );

        if (! $this->reader) {
            throw new LogicException(sprintf(
                'The annotation reader has not been set. Override isTransient() in %s instead.',
                static::class
            ));
        }

        $classAnnotations = $this->reader->getClassAnnotations(new ReflectionClass($className));

        foreach ($classAnnotations as $annot) {
            if (isset($this->entityAnnotationClasses[get_class($annot)])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if (! $this->paths) {
            throw MappingException::pathRequired();
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

                if (! preg_match('(^phar:)i', $sourceFile)) {
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
            $rc         = new ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (! in_array($sourceFile, $includedFiles) || $this->isTransient($className)) {
                continue;
            }

            $classes[] = $className;
        }

        $this->classNames = $classes;

        return $classes;
    }
}
