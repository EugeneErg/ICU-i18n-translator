<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Translators\Contracts;

interface TransliterationLanguageTranslatorInterface extends TranslatorInterface
{
    public function romanization(string $fromLanguage, string $value): string;

    public function getTransliterationLanguages(): array;
}