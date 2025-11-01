<?php

declare(strict_types=1);

use Doctrine\Persistence\Mapping\ClassMetadata;

return static function (ClassMetadata $metadata): void {
    $metadata->getFieldNames();
};
