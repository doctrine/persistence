<?php

namespace Doctrine\Common\Persistence\Mapping;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Mapping\ClassMetadata::class,
    __NAMESPACE__ . '\ClassMetadata'
);

if (false) {
    interface ClassMetadata extends \Doctrine\Persistence\Mapping\ClassMetadata
    {
    }
}
