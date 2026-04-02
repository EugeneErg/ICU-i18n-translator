<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Translators\GoogleInformalTranslate;

use EugeneErg\Translate\Clients\GoogleInformalTranslate\Client;
use EugeneErg\Translate\Clients\GoogleInformalTranslate\ValueObjects\GoogleTranslateType;
use EugeneErg\Translate\Translators\Contracts\TranslatorInterface;
use EugeneErg\Translate\Translators\Contracts\TransliterationLanguageTranslatorInterface;
use EugeneErg\Translate\Translators;

readonly class GoogleInformalTranslator implements
    TranslatorInterface,
    TransliterationLanguageTranslatorInterface
{
    public function __construct(private Client $client)
    {
    }

    public function translate(array $pattern, string $fromLocale, string $toLocale, ?string $context = null,): array
    {
        // TODO: Implement translate() method.
    }

    public function translate2(
        string  $pattern,
        string  $fromLocale,
        string  $toLocale,
        array   $values = [],
        ?string $context = null,
    ): string {
        $replace = [];

        foreach ($values as $number => $variable) {
            $replace["{{{$variable}}}"] = "{{_{$number}_}}";
        }

        $result = $this->client->single(
            text: $pattern,
            targetLanguage: $toLocale,
            types: [GoogleTranslateType::Translation],
            sourceLanguage: $fromLocale,
        );

        return str_replace(array_values($replace), array_keys($replace), $result[0]->text);
    }

    public function translateWithDetect(
        string $template,
        string $toLanguage,
        array $variables = [],
        ?string $context = null,
    ): Translation {
        $replace = [];

        foreach ($variables as $number => $variable) {
            $replace['{{' . $variable . '}}'] = "{{_{$number}_}}";
        }

        $result = $this->client->single(
            text: $template,
            targetLanguage: $toLanguage,
            types: [GoogleTranslateType::Translation],
        );

        return new Translation(
            $result->detectedSourceLanguage,
            str_replace(array_values($replace), array_keys($replace), $result[0]->text),
        );
    }

    public function getTargetLanguages(): array
    {
        return array_keys($this->client->getSupportedLanguages()->languages);
    }

    public function romanization(string $fromLanguage, string $value): string
    {
        $result = $this->client->single(
            text: $value,
            targetLanguage: 'en',
            types: [GoogleTranslateType::Romanization],
            sourceLanguage: $fromLanguage,
        );

        return $result->translates[0]->translatedText;
    }

    public function getTransliterationLanguages(): array
    {
        return $this->getTargetLanguages();
    }

    public function canTranslate(string $toLocale, ?string $fromLocale = null): bool
    {
        // TODO: Implement canTranslate() method.
    }
}