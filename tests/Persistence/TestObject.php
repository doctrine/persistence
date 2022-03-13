<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

class TestObject
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
