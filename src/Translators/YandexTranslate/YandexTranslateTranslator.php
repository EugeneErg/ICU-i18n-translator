<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Translators\YandexTranslate;

use EugeneErg\IcuI18nTranslator\Translators\Contracts\DetectLanguageTranslatorInterface;
use EugeneErg\IcuI18nTranslator\Translators\Contracts\TranslatorInterface;
use EugeneErg\IcuI18nTranslator\Translators\Contracts\TransliterationLanguageTranslatorInterface;
use EugeneErg\IcuI18nTranslator\Translators;

readonly class YandexTranslateTranslator implements TranslatorInterface, DetectLanguageTranslatorInterface, TransliterationLanguageTranslatorInterface
{
    public function detect(string $value): string
    {
        // TODO: Implement detect() method.
    }

    public function translate(string $pattern, string $fromLocale, string $toLocale, array $values = [], ?string $context = null,): string
    {
        // TODO: Implement translate() method.
    }

    public function translateWithDetect(string $template, string $toLanguage, array $variables = [], ?string $context = null,): Translation
    {
        // TODO: Implement translateWithDetect() method.
    }

    public function getLanguages(): array
    {
        // TODO: Implement getLanguages() method.
    }

    public function romanization(string $fromLanguage, string $value): string
    {
        // TODO: Implement transliteration() method.
    }

    public function getTransliterationLanguages(): array
    {
        // TODO: Implement getTransliterationLanguages() method.
    }
}