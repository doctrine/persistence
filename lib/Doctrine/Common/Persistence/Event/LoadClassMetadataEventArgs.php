<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;
use function class_exists;

if (!class_exists(__NAMESPACE__ . '\LoadClassMetadataEventArgs')) {
    class_alias(
        \Doctrine\Persistence\Event\LoadClassMetadataEventArgs::class,
        __NAMESPACE__ . '\LoadClassMetadataEventArgs'
    );
}

if (false) {
    class LoadClassMetadataEventArgs extends \Doctrine\Persistence\Event\LoadClassMetadataEventArgs
    {
    }
}
