<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\ValueObjects;

enum ProfanityAction: string
{
    case NoAction = 'NoAction';
    case Marked = 'Marked';
    case Deleted = 'Deleted';
}