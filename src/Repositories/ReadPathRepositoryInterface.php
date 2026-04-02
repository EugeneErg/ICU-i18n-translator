<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Path;
use EugeneErg\Translate\ValueObjects\PathId;

interface ReadPathRepositoryInterface
{
    public function findRoot(string $value): ?Path;

    /**
     * @return Path[]
     */
    public function listByParentId(PathId $parentId): array;

    public function findChild(string $value, PathId $parentId): ?Path;
}