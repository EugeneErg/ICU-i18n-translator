<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Repositories;

use EugeneErg\IcuI18nTranslator\Entities\Translate;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;
use EugeneErg\IcuI18nTranslator\ValueObjects\TranslateId;

interface WriteTranslateRepositoryInterface
{
    public function create(string $pattern, string $locale): Translate;

    public function delete(TranslateId $translateId): void;
}