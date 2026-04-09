<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Translate;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;

interface WriteTranslateRepositoryInterface
{
    public function create(string $pattern, string $locale): Translate;
}