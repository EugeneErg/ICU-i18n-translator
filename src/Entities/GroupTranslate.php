<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Entities;

use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\TranslateId;

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