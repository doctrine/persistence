<?php

namespace Doctrine\Common\Persistence\Mapping;

use const E_USER_DEPRECATED;
use function class_alias;
use function class_exists;
use function sprintf;
use function trigger_error;

if (! class_exists(\Doctrine\Persistence\Mapping\MappingException::class, false)) {
    @trigger_error(sprintf(
        'The %s\MappingException class is deprecated since doctrine/persistence 1.3 and will be removed in 2.0.'
        . ' Use \Doctrine\Persistence\Mapping\MappingException instead.',
        __NAMESPACE__
    ), E_USER_DEPRECATED);
}

class_alias(
    \Doctrine\Persistence\Mapping\MappingException::class,
    __NAMESPACE__ . '\MappingException'
);

if (false) {
    /**
     * @deprecated 1.3 Use Doctrine\Persistence\Mapping\MappingException
     */
    class MappingException extends \Doctrine\Persistence\Mapping\MappingException
    {
    }
}
