<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Group;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;

interface ReadGroupRepositoryInterface
{
    public function findByPattern(string $originalPattern, string|null $context, string|null $locale = null): Group|null;

    public function find(GroupId $id): Group|null;

    /**
     * @return Group[]
     */
    public function list(int $offset, int $limit): array;
}
