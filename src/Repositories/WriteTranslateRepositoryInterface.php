<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Translate;
use EugeneErg\Translate\ValueObjects\GroupId;

interface WriteTranslateRepositoryInterface
{
    public function create(string $pattern, string $locale): Translate;
}