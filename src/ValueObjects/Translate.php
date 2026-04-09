<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\ValueObjects;

use EugeneErg\IcuI18nTranslator\DataTransferObjects\Variable;

final readonly class Translate
{
    /** @var array<string|Variable> */
    public array $value;

    public function __construct(public ?array $replaced = [], string|Variable ...$value)
    {
    }

    public function changeVariable(callable $callback): Translated
    {
        $replaced = [];
        $result = '';

        foreach ($this->value as $value) {
            if ($value instanceof Variable) {
                $replaced[$value] = $callback($value->value);
                $result .= $replaced[$value];
            } else {
                $result .= $value;
            }
        }

        return new Translated($result, $replaced);
    }
}