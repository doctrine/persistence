<?php

namespace Doctrine\Persistence\Mapping;

use Doctrine\Persistence\Proxy;

interface ProxyClassNameResolver
{
    /**
     * @template T of object
     * @psalm-param class-string<Proxy<T>>|class-string<T> $className
     * @psalm-return class-string<T>
     */
    public function resolveClassName(string $className): string;
}