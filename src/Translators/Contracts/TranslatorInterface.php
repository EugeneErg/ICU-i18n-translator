<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Translators\Contracts;

use EugeneErg\Translate\DataTransferObjects\Variable;
use EugeneErg\Translate\Translators;
use EugeneErg\Translate\ValueObjects\Translated;

interface TranslatorInterface
{
    /**
     * @param array<string|Variable> $pattern
     *
     * @return array<string|Variable>
     */
    public function translate(
        array $pattern,
        string $fromLocale,
        string $toLocale,
        ?string $context = null,
    ): array;

    /**
     * @param array<string|Variable> $pattern
     */
    public function translateWithDetect(
        array $pattern,
        string $toLocale,
        ?string $context = null,
    ): Translated;

    public function canTranslate(string $toLocale, ?string $fromLocale = null): bool;
}