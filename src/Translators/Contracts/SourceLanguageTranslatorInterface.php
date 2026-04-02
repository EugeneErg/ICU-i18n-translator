<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Translators\Contracts;

interface SourceLanguageTranslatorInterface extends TranslatorInterface
{
    /** @return string[] */
    public function getSourceLanguages(): array;
}