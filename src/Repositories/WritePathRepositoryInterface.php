<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Path;
use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\PathId;

interface WritePathRepositoryInterface
{
    public function create(
        string $value,
        ?PathId $parentId = null,
        ?GroupId $groupId = null,
    ): Path;
}