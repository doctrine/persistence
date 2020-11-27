<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\OnClearEventArgs')) {
    class_alias(
        \Doctrine\Persistence\Event\OnClearEventArgs::class,
        __NAMESPACE__ . '\OnClearEventArgs'
    );
}

if (false) {
    class OnClearEventArgs extends \Doctrine\Persistence\Event\OnClearEventArgs
    {
    }
}
