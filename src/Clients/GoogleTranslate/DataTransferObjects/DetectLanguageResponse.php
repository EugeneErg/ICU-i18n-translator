<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/DetectLanguageResponse
 */
final readonly class DetectLanguageResponse
{
    /**
     * @param DetectLanguage[] $languages
     */
    public function __construct(
        array $languages,
    ) {
    }
}