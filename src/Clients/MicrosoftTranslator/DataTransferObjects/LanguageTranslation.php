<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects\LanguageDirection;

final readonly class LanguageTranslation
{
    public function __construct(
        public string $name,
        public string $nativeName,
        public LanguageDirection $direction,
    ) {
    }
}