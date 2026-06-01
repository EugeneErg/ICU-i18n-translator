<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Entities;

use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;
use EugeneErg\IcuI18nTranslator\ValueObjects\PathId;

final readonly class Path
{
    public function __construct(
        public PathId $id,
        public string $value,
        public PathId|null $parentId = null,
        public GroupId|null $groupId = null,
    ) {
    }
}
