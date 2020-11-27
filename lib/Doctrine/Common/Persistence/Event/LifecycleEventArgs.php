<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\LifecycleEventArgs')) {
    class_alias(
        \Doctrine\Persistence\Event\LifecycleEventArgs::class,
        __NAMESPACE__ . '\LifecycleEventArgs'
    );
}

if (false) {
    class LifecycleEventArgs extends \Doctrine\Persistence\Event\LifecycleEventArgs
    {
    }
}
