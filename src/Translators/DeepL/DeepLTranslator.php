<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Translators\DeepL;

use EugeneErg\IcuI18nTranslator\Clients\DeepL\Client;
use EugeneErg\IcuI18nTranslator\Clients\DeepL\Exceptions\DeepLClientExceptionInterface;
use EugeneErg\IcuI18nTranslator\Clients\DeepL\ValueObjects\LanguageType;
use EugeneErg\IcuI18nTranslator\Clients\DeepL\ValueObjects\TagHandling;
use EugeneErg\IcuI18nTranslator\Translators\Contracts\TranslatorInterface;
use EugeneErg\IcuI18nTranslator\Translators;

readonly class DeepLTranslator implements TranslatorInterface
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @param array<int, string> $values
     *
     * @throws DeepLClientExceptionInterface
     */
    public function translate(
        string  $pattern,
        string  $fromLocale,
        string  $toLocale,
        array   $values = [],
        ?string $context = null,
    ): string {
        $replace = [];

        foreach ($values as $variable) {
            $replace['{{' . $variable . '}}'] = '<v>' . $variable . '</v>';
        }

        $text = [str_replace(array_keys($replace), array_values($replace), $pattern)];
        $result = $this->client->translate(
            text: $text,
            targetLang: $toLocale,
            sourceLang: $fromLocale,
            context: $context,
            tagHandling: TagHandling::Xml,
            ignoreTags: ['v'],
        );

        return str_replace(array_values($replace), array_keys($replace), $result[0]->text);
    }

    /**
     * @param array<int, string> $variables
     *
     * @throws DeepLClientExceptionInterface
     */
    public function translateWithDetect(
        string $template,
        string $toLanguage,
        array $variables = [],
        ?string $context = null,
    ): Translation {
        $replace = [];

        foreach ($variables as $variable) {
            $replace['{{' . $variable . '}}'] = '<v>' . $variable . '</v>';
        }

        $text = [str_replace(array_keys($replace), array_values($replace), $template)];
        $result = $this->client->translate(
            text: $text,
            targetLang: $toLanguage,
            context: $context,
            tagHandling: TagHandling::Xml,
            ignoreTags: ['v'],
        );

        return new Translation(
            language: $result[0]->detectedSourceLanguage,
            value: str_replace(array_values($replace), array_keys($replace), $result[0]->text),
        );
    }

    /**
     * @throws DeepLClientExceptionInterface
     */
    public function getTargetLanguages(): array
    {
        return array_column($this->client->getLanguages(LanguageType::Target), 'language');
    }
}