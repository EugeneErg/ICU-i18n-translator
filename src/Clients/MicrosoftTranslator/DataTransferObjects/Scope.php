<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\DataTransferObjects;

enum Scope: string
{
    case Translation = 'translation';
    case Transliteration = 'transliteration';
    case Dictionary = 'dictionary';
}