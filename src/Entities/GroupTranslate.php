<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Entities;

use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;
use EugeneErg\IcuI18nTranslator\ValueObjects\TranslateId;

final readonly class GroupTranslate
{
    public function __construct(
        public GroupId $groupId,
        public TranslateId $translateId,
        public string $key,
        public ?TranslateId $sourceId,
    ) {
    }
}