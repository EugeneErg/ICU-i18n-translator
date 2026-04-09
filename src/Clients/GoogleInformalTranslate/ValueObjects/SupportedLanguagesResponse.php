<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class SupportedLanguagesResponse
{
    /**
     * @param Language[] $languages
     * @param mixed[] $al
     */
    public function __construct(public array $languages, public array $al)
    {
    }
}