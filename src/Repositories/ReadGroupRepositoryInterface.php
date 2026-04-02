<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Group;
use EugeneErg\Translate\ValueObjects\GroupId;

interface ReadGroupRepositoryInterface
{
    public function findByPattern(string $originalPattern, ?string $context, ?string $locale = null): ?Group;
    public function find(GroupId $id): ?Group;
}