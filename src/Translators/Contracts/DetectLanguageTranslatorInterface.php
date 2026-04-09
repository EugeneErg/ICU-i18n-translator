<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Translators\Contracts;

interface DetectLanguageTranslatorInterface extends TranslatorInterface
{
    public function detect(string $value): string;
}