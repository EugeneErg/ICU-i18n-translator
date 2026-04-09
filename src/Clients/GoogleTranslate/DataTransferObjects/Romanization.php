<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\DataTransferObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/RomanizeTextResponse#Romanization
 */
final readonly class Romanization
{
    public function __construct(
        public string $romanizedText,
        public ?string $detectedLanguageCode = null,
    ) {
    }
}