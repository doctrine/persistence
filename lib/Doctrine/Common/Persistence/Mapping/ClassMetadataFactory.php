<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ClassMetadataFactory')) {
    class_alias(
    \Doctrine\Persistence\Mapping\ClassMetadataFactory::class,
    __NAMESPACE__ . '\ClassMetadataFactory'
    );
}

if (false) {
    interface ClassMetadataFactory extends \Doctrine\Persistence\Mapping\ClassMetadataFactory
    {
    }
}
