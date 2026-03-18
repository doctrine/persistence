<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;

use function method_exists;

/**
 * The StaticPHPDriver calls a static loadMetadata() method on your entity
 * classes where you can manually populate the ClassMetadata instance.
 *
 * @final since 4.2
 */
class StaticPHPDriver implements MappingDriver
{
    use ColocatedMappingDriver {
        addPaths as private;
        getPaths as private;
        addExcludePaths as private;
        getExcludePaths as private;
        getFileExtension as private;
        setFileExtension as private;
    }

    /** @param array<int, string>|string|ClassLocator $paths */
    public function __construct(array|string|ClassLocator $paths)
    {
        if ($paths instanceof ClassLocator) {
            $this->classLocator = $paths;
        } else {
            $this->addPaths((array) $paths);
        }
    }

    public function loadMetadataForClass(string $className, ClassMetadata $metadata): void
    {
        $className::loadMetadata($metadata);
    }

    public function isTransient(string $className): bool
    {
        return ! method_exists($className, 'loadMetadata');
    }
}
