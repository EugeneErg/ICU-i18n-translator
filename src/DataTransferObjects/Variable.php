<?php

declare(strict_types=1);

namespace EugeneErg\IcuI18nTranslator\DataTransferObjects;

final readonly class Variable
{
    public function __construct(public int $value)
    {
    }
}
