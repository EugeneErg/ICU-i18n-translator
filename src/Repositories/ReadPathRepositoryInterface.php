<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Path;
use EugeneErg\IcuI18nTranslator\ValueObjects\PathId;

interface ReadPathRepositoryInterface
{
    public function findRoot(string $value): Path|null;

    /**
     * @return Path[]
     */
    public function listRoot(int $offset, int $limit): array;

    /**
     * @return Path[]
     */
    public function listByParentId(PathId $parentId): array;

    public function findChild(string $value, PathId $parentId): Path|null;

    public function findById(PathId $id): Path|null;
}
