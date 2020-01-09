<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Event\LifecycleEventArgs::class,
    __NAMESPACE__ . '\LifecycleEventArgs'
);

if (false) {
    class LifecycleEventArgs extends \Doctrine\Persistence\Event\LifecycleEventArgs
    {
    }
}
