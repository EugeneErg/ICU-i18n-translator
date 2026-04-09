<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class DetectLanguage
{
    public function __construct(
        public Detect $detect,
        public array $alternatives = [],
    ) {
    }
}