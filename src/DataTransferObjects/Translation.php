<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\DataTransferObjects;

final readonly class Translation
{
    public function __construct(public string $language, public string $value)
    {
    }
}