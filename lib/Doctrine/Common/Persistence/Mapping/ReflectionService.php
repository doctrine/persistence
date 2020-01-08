<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\ReflectionService::class,
    __NAMESPACE__ . '\ReflectionService'
);

if (false) {
    interface ReflectionService extends \Doctrine\Persistence\Mapping\ReflectionService
    {
    }
}
