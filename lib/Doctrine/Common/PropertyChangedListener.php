<?php

namespace Doctrine\Common;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\PropertyChangedListener')) {
    class_alias(
        \Doctrine\Persistence\PropertyChangedListener::class,
        __NAMESPACE__ . '\PropertyChangedListener'
    );
}

if (false) {
    interface PropertyChangedListener extends \Doctrine\Persistence\PropertyChangedListener
    {
    }
}
