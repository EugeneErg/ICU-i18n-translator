<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\GoogleTranslate\ValueObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/projects/translateText#body.request_body.FIELDS.mime_type
 */
enum MimeType: string
{
    case Plain = 'text/plain';
    case Html = 'text/html';
}