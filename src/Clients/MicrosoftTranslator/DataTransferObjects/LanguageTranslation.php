<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\DataTransferObjects;

use EugeneErg\Translate\Clients\MicrosoftTranslator\ValueObjects\LanguageDirection;

final readonly class LanguageTranslation
{
    public function __construct(
        public string $name,
        public string $nativeName,
        public LanguageDirection $direction,
    ) {
    }
}