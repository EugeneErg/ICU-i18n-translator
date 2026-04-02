<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Translators\MicrosoftTranslator;

use EugeneErg\Translate\Clients\MicrosoftTranslator\Client;
use EugeneErg\Translate\Clients\MicrosoftTranslator\TranslationText;
use EugeneErg\Translate\Clients\MicrosoftTranslator\Exceptions\MicrosoftTranslatorExceptionInterface;
use EugeneErg\Translate\Translators\Contracts\DetectLanguageTranslatorInterface;
use EugeneErg\Translate\Translators\Contracts\TranslatorInterface;
use EugeneErg\Translate\Translators\Contracts\TransliterationLanguageTranslatorInterface;
use EugeneErg\Translate\Translators;
use IntlChar;

readonly class MicrosoftTranslatorTranslator implements
    TranslatorInterface,
    DetectLanguageTranslatorInterface,
    TransliterationLanguageTranslatorInterface
{
    private const SCRIPTS = [
        'Arabic' => 'Arab',
        'Bengali' => 'Beng',
        'Cyrillic' => 'Cyrl',
        'Chinese Simplified' => 'Hans',
        'Chinese Traditional' => 'Hant',
        'Greek' => 'Grek',
        'Gujarati' => 'Gujr',
        'Hebrew' => 'Hebr',
        'Devanagari' => 'Deva',
        'Japanese' => 'Jpan',
        'Kannada' => 'Knda',
        'Korean' => 'Kore',
        'Malayalam' => 'Mlym',
        'Oriya' => 'Orya',
        'Gurmukhi' => 'Guru',
        'Latin' => 'Latn',
        'Sinhala' => 'Sinh',
        'Tamil' => 'Taml',
        'Telugu' => 'Telu',
        'Thai' => 'Thai',
    ];

    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @throws MicrosoftTranslatorExceptionInterface
     */
    public function detect(string $value): string
    {
        $result = $this->client->detect([new TranslationText($value)]);

        return $result[0]->detect->language;
    }

    /**
     * @throws MicrosoftTranslatorExceptionInterface
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
            $replace['{{' . $variable . '}}'] = '<div class="notranslate" translate="no">' . $variable . '</div>';
        }

        $text = str_replace(array_keys($replace), array_values($replace), $pattern);
        $result = $this->client->translate([new TranslationText($text)], $toLocale, $fromLocale);

        return str_replace(array_values($replace), array_keys($replace), $result->translations[0]->text);
    }

    /**
     * @throws MicrosoftTranslatorExceptionInterface
     */
    public function translateWithDetect(
        string $template,
        string $toLanguage,
        array $variables = [],
        ?string $context = null,
    ): Translation {
        $replace = [];

        foreach ($variables as $variable) {
            $replace['{{' . $variable . '}}'] = '<div class="notranslate" translate="no">' . $variable . '</div>';
        }

        $text = str_replace(array_keys($replace), array_values($replace), $template);
        $result = $this->client->translate([new TranslationText($text)], $toLanguage);

        return new Translation(
            $result->detectedLanguage->language,
            str_replace(array_values($replace), array_keys($replace), $result->translations[0]->text),
        );
    }

    public function getLanguages(): array
    {
        // TODO: Implement getLanguages() method.
    }

    public function romanization(string $fromLanguage, string $value): string
    {
        $script = $this->detectScript($value);

        if ($script === null) {
            return $value;
        }

        $result = $this->client->transliterate([new TranslationText($value)], $fromLanguage, $script, 'Latn');

        return $result[0]->text;
    }

    public function getTransliterationLanguages(): array
    {
        // TODO: Implement getTransliterationLanguages() method.
    }

    private function detectScript(string $text): ?string
    {
        $scripts = [];
        $text = preg_split('{}u', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($text as $char) {
            $script = $this->getCharScript($char);

            if ($script === 'Unknown') {
                continue;
            }

            $scripts[$script] = ($scripts[$script] ?? 0) + 1;
        }

        arsort($scripts);

        return array_key_first($scripts);
    }

    private function getCharScript(string $char): string
    {
        return self::SCRIPTS[IntlChar::getPropertyValueName(
            IntlChar::PROPERTY_SCRIPT,
            IntlChar::getIntPropertyValue(IntlChar::ord($char), IntlChar::PROPERTY_SCRIPT)
        )] ?? 'Unknown';
    }
}
