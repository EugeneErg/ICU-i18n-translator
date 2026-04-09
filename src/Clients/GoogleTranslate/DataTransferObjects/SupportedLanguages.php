<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\DataTransferObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/SupportedLanguages
 */
final readonly class SupportedLanguages
{
    /**
     * @param SupportedLanguage[] $languages
     */
    public function __construct(
        public array $languages,
    ) {
    }
}