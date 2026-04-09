<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Translators\Contracts;

interface DirectionLanguageTranslatorInterface extends TranslatorInterface
{
    /** @return array<string, string[]> */
    public function getDirectionLanguages(): array;
}