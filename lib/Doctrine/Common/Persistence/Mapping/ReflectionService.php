<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ReflectionService')) {
    class_alias(
        \Doctrine\Persistence\Mapping\ReflectionService::class,
        __NAMESPACE__ . '\ReflectionService'
    );
}

if (false) {
    interface ReflectionService extends \Doctrine\Persistence\Mapping\ReflectionService
    {
    }
}
