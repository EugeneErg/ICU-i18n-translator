<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\GroupTranslate;
use EugeneErg\Translate\ValueObjects\GroupId;

interface ReadGroupTranslateRepositoryInterface
{
    /**
     * @return GroupTranslate[]
     */
    public function list(GroupId $groupId, string $locale): array;
}