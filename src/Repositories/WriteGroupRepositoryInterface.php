<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Group;
use EugeneErg\Translate\ValueObjects\GroupId;

interface WriteGroupRepositoryInterface
{
    public function create(string $originalPattern, string $pattern, ?string $context, string $locale): Group;

    public function delete(GroupId $id): void;
}