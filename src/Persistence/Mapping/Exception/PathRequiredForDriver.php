<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Exception;

use function sprintf;

final class PathRequiredForDriver extends MappingException
{
    /**
     * @param class-string $driverClassName
     */
    public static function create(string $driverClassName): self
    {
        return new self(sprintf(
            'Specifying the paths to your entities is required when using "%s" to retrieve all class names.',
            $driverClassName
        ));
    }
}
