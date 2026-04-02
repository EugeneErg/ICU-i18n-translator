<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\DeepL\ValueObjects;

enum LanguageType: string
{
    case Source = 'source';
    case Target = 'target';
}