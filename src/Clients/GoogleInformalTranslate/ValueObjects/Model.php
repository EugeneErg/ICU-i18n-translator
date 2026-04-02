<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleInformalTranslate\ValueObjects;

final readonly class Model
{
    public function __construct(
        public string $hash,
        public string $fileName,
    ) {
    }
}