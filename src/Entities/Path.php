<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Entities;

use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\PathId;

final readonly class Path
{
    public function __construct(
        public PathId $id,
        public string $value,
        public ?PathId $parentId = null,
        public ?GroupId $groupId = null,
    ) {
    }
}