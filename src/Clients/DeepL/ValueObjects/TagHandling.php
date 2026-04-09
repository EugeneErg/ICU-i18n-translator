<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\DeepL\ValueObjects;

enum TagHandling: string
{
    case Xml = 'xml';
    case Html = 'html';
}