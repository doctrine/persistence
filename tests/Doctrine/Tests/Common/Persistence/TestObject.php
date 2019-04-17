<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\PersistentObject;

class TestObject extends PersistentObject
{
    /** @var int */
    protected $id = 1;

    /** @var string */
    protected $name = 'beberlei';

    /** @var TestObject */
    protected $parent;

    /** @var TestObject */
    protected $children;
}
