<?php

use Doctrine\Persistence\Mapping\ClassMetadata;

return static function (ClassMetadata $metadata): void
{
    $metadata->getFieldNames();
};
