<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class SentLen
{
    /**
     * @param int[] $srcSentLen
     * @param int[] $transSentLen
     */
    public function __construct(
        public array $srcSentLen,
        public array $transSentLen,
    ) {
    }
}