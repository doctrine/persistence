<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\RuntimeReflectionService::class,
    __NAMESPACE__ . '\RuntimeReflectionService'
);

if (false) {
    class RuntimeReflectionService extends \Doctrine\Persistence\Mapping\RuntimeReflectionService
    {
    }
}
