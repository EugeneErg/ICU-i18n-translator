<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/DetectLanguageResponse#DetectedLanguage
 */
final readonly class DetectLanguage
{
    public function __construct(
        public string $languageCode,
        public float $confidence,
    ) {
    }
}