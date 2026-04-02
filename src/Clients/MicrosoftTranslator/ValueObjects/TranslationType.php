<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\MicrosoftTranslator\ValueObjects;

enum TranslationType: string
{
    case Plain = 'plain';
    case Html = 'html';
}