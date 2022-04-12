<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Exception;

use function sprintf;

final class InvalidMappingFile extends MappingException
{
    public static function create(string $entityName, string $fileName): self
    {
        return new self(sprintf(
            'Invalid mapping file "%s" for class "%s".',
            $fileName,
            $entityName
        ));
    }
}
