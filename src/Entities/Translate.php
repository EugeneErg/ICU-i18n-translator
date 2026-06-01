<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\Entities;

use EugeneErg\IcuI18nTranslator\ValueObjects\TranslateId;

final readonly class Translate
{
    public function __construct(
        public TranslateId $id,
        public string $pattern,
        public string $locale,
    ) {
    }
}
