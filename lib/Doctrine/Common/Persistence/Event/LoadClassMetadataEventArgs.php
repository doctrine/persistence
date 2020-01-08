<?php

namespace Doctrine\Common\Persistence\Event;

use function class_alias;

class_alias(
    \Doctrine\Persistence\Event\LoadClassMetadataEventArgs::class,
    __NAMESPACE__ . '\LoadClassMetadataEventArgs'
);

if (false) {
    class LoadClassMetadataEventArgs extends \Doctrine\Persistence\Event\LoadClassMetadataEventArgs
    {
    }
}
