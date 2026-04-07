<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Translate;
use EugeneErg\Translate\ValueObjects\GroupId;

interface ReadTranslateRepositoryInterface
{
    public function find(string $pattern, ?string $locale = null): ?Translate;

    public function findByGroup(GroupId $groupId, string $key, string $locale): ?Translate;

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