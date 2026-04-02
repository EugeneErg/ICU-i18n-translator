<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate\DataTransferObjects;

/**
 * @link https://cloud.google.com/translate/docs/reference/rest/v3/RomanizeTextResponse
 */
final readonly class RomanizeTextResponse
{
    /**
     * @param array $romanizations
     */
    public function __construct(
        array $romanizations,
    ) {
    }
}