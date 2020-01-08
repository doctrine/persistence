<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\MappingException::class,
    __NAMESPACE__ . '\MappingException'
);

if (false) {
    class MappingException extends \Doctrine\Persistence\Mapping\MappingException
    {
    }
}
