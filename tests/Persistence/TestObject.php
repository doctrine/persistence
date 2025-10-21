<?php

declare(strict_types=1);

namespace Doctrine\Tests\Persistence;

class TestObject
{
    protected int $id = 1;

    protected string $name = 'beberlei';

    protected TestObject $parent;

    protected TestObject $children;
}
