<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\ManagerEventArgs')) {
    class_alias(
        \Doctrine\Persistence\Event\ManagerEventArgs::class,
        __NAMESPACE__ . '\ManagerEventArgs'
    );
}

if (false) {
    class ManagerEventArgs extends \Doctrine\Persistence\Event\ManagerEventArgs
    {
    }
}
