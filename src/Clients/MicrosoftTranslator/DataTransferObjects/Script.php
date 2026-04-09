<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DataTransferObjects;

final readonly class Script
{
    /**
     * @param LanguageTranslation[] $toScripts
     */
    public function __construct(
        public LanguageTranslation $fromScript,
        public array $toScripts,
    ) {
    }
}