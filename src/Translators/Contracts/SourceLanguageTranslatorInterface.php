<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Translators\Contracts;

interface SourceLanguageTranslatorInterface extends TranslatorInterface
{
    /** @return string[] */
    public function getSourceLanguages(): array;
}