<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\AbstractClassMetadataFactory::class,
    __NAMESPACE__ . '\AbstractClassMetadataFactory'
);

if (false) {
    class AbstractClassMetadataFactory extends \Doctrine\Persistence\Mapping\AbstractClassMetadataFactory
    {
    }
}
