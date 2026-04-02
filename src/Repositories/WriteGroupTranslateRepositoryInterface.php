<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\GroupTranslate;
use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\TranslateId;

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