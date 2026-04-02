<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

final readonly class LocationModelGlossary
{
    public function __construct(
        public ?string $location = null,
        public ?string $model = null,
        public ?TranslateTextGlossaryConfig $glossaryConfig = null,
    ) {
    }
}