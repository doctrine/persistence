<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\RuntimeReflectionService')) {
    class_alias(
        \Doctrine\Persistence\Mapping\RuntimeReflectionService::class,
        __NAMESPACE__ . '\RuntimeReflectionService'
    );
}

if (false) {
    class RuntimeReflectionService extends \Doctrine\Persistence\Mapping\RuntimeReflectionService
    {
    }
}
