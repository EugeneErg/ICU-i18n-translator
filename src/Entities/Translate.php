<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Entities;

use EugeneErg\Translate\ValueObjects\TranslateId;

final readonly class Translate
{
    public function __construct(
        public TranslateId $id,
        public string $pattern,
        public string $locale,
    ) {
    }
}