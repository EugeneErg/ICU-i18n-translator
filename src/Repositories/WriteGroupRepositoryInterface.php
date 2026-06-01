<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Group;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;

interface WriteGroupRepositoryInterface
{
    public function create(string $originalPattern, string $pattern, string|null $context, string $locale): Group;

    public function delete(GroupId $id): void;
}
