<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Exception;

use function sprintf;

final class NonExistingClass extends MappingException
{
    public static function create(string $className): self
    {
        return new self(sprintf('Class "%s" does not exist', $className));
    }
}
