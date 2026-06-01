<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Path;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;
use EugeneErg\IcuI18nTranslator\ValueObjects\PathId;

interface WritePathRepositoryInterface
{
    public function create(
        string $value,
        PathId|null $parentId = null,
        GroupId|null $groupId = null,
    ): Path;

    public function delete(PathId $id): void;
}
