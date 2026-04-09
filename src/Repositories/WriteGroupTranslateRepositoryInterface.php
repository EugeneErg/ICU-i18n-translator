<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\GroupTranslate;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;
use EugeneErg\IcuI18nTranslator\ValueObjects\TranslateId;

interface WriteGroupTranslateRepositoryInterface
{
    public function create(
        GroupId $groupId,
        TranslateId $translateId,
        string $key,
        ?TranslateId $sourceId = null,
    ): GroupTranslate;

    public function deleteByGroupId(GroupId $groupId): void;
}