<?php

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;

use function get_class;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 *
 * @deprecated use ColocatedMappingDriver directly instead.
 */
abstract class AnnotationDriver implements MappingDriver
{
    use ColocatedMappingDriver;

    /**
     * The annotation reader.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Name of the entity annotations as keys.
     *
     * @var array<class-string, bool|int>
     */
    protected $entityAnnotationClasses = [];

    /**
     * Initializes a new AnnotationDriver that uses the given AnnotationReader for reading
     * docblock annotations.
     *
     * @param Reader               $reader The AnnotationReader to use, duck-typed.
     * @param string|string[]|null $paths  One or multiple paths where mapping classes can be found.
     */
    public function __construct($reader, $paths = null)
    {
        $this->reader = $reader;

        $this->addPaths((array) $paths);
    }

    /**
     * Retrieve the current annotation reader
     *
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient($className)
    {
        $classAnnotations = $this->reader->getClassAnnotations(new ReflectionClass($className));

        foreach ($classAnnotations as $annot) {
            if (isset($this->entityAnnotationClasses[get_class($annot)])) {
                return false;
            }
        }

        return true;
    }
}
