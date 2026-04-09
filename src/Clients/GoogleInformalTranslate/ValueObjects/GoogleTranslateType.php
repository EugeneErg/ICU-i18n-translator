<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleInformalTranslate\ValueObjects;

enum GoogleTranslateType: string
{
    case Translation = 't';
    case AlternativeTranslations = 'at';
    case Romanization = 'rm';
    case Dictionary = 'bd';
    case QualityCheck = 'qc';
    case Examples = 'ex';
    case Synonyms = 'ss';
    case RelatedWords = 'rw';
    case Morphology = 'md';
}