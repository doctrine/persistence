<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\StaticReflectionService::class,
    __NAMESPACE__ . '\StaticReflectionService'
);

if (false) {
    class StaticReflectionService extends \Doctrine\Persistence\Mapping\StaticReflectionService
    {
    }
}
