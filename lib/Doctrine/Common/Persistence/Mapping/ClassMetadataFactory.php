<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\ClassMetadataFactory::class,
    __NAMESPACE__ . '\ClassMetadataFactory'
);

if (false) {
    interface ClassMetadataFactory extends \Doctrine\Persistence\Mapping\ClassMetadataFactory
    {
    }
}
