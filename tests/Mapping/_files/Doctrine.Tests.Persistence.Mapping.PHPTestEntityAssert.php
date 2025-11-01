<?php

declare(strict_types=1);

use Doctrine\Persistence\Mapping\ClassMetadata;

assert($metadata instanceof ClassMetadata);
$metadata->getFieldNames();
