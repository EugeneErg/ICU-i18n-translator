<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\DataTransferObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/SupportedLanguages#SupportedLanguage
 */
final readonly class SupportedLanguage
{
    public function __construct(
        public string $languageCode,
        public string $displayName,
        public bool $supportSource,
        public bool $supportTarget,
    ) {
    }
}