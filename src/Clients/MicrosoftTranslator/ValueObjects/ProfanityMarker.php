<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects;

enum ProfanityMarker: string
{
    case Asterisk = 'Asterisk';
    case Tag = 'Tag';
}