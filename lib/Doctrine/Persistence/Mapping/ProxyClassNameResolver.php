<?php

namespace Doctrine\Persistence\Mapping;

interface ProxyClassNameResolver
{
    /**
     * @psalm-param class-string $className
     * @psalm-return class-string
     */
    public function resolveClassName(string $className): string;
}
