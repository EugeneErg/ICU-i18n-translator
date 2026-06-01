<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Translate;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;

interface ReadTranslateRepositoryInterface
{
    public function find(string $pattern, string|null $locale = null): Translate|null;

    public function findByGroup(GroupId $groupId, string $key, string $locale): Translate|null;

    /**
     * @return array<string, Translate>
     */
    public function groupListByKey(GroupId $groupId, string $locale): array;

    /**
     * @param string[] $keys
     *
     * @return array<string, Translate>
     */
    public function keysListByKey(GroupId $groupId, string $locale, array $keys): array;
}
