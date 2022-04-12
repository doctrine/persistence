<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Exception;

use function sprintf;

final class MappingFileNotFound extends MappingException
{
    public static function create(string $entityName, string $fileName): self
    {
        return new self(sprintf(
            'No mapping file found named "%s" for class "%s".',
            $fileName,
            $entityName
        ));
    }
}
