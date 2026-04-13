<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Group;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;

interface ReadGroupRepositoryInterface
{
    public function findByPattern(string $originalPattern, ?string $context, ?string $locale = null): ?Group;
    public function find(GroupId $id): ?Group;

    /**
     * @return Group[]
     */
    public function list(int $offset, int $limit): array;
}