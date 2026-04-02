<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Translators\Contracts;

interface DetectLanguageTranslatorInterface extends TranslatorInterface
{
    public function detect(string $value): string;
}