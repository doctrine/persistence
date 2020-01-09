<?php

namespace Doctrine\Common;

use function class_alias;

class_alias(
    \Doctrine\Persistence\PropertyChangedListener::class,
    __NAMESPACE__ . '\PropertyChangedListener'
);

if (false) {
    interface PropertyChangedListener extends \Doctrine\Persistence\PropertyChangedListener
    {
    }
}
