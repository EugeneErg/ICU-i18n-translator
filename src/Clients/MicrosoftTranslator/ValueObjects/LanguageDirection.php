<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\ValueObjects;

enum LanguageDirection: string
{
    case RightToLeft = 'rtl';
    case LeftToRight = 'ltr';
}