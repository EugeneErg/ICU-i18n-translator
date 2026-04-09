<?php

declare(strict_types = 1);

namespace EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator;

use EugeneErg\IcuI18nTranslator\Clients\Contracts\ClientInterface;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Detect;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\DetectLanguage;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Dictionary;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\LanguageListResponse;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\LanguageTranslation;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\LanguageTransliteration;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Scope;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Script;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\TranslateResponse;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Translation;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\TranslationText;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Transliteration;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\AuthenticationException;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\ClientException;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\InvalidRequestException;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\InvalidResponseException;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\MicrosoftTranslatorExceptionInterface;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\NetworkException;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\Exceptions\RequestException;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects\AuthorizationInterface;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects\LanguageDirection;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects\ProfanityAction;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects\ProfanityMarker;
use EugeneErg\IcuI18nTranslator\Clients\MicrosoftTranslator\ValueObjects\TranslationType;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Throwable;

readonly class Client
{
    public function __construct(
        private string $apiUrl,
        private ClientInterface $client,
        private AuthorizationInterface $authorization,
    ) {
    }

    /**
     * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-translate
     *
     * @param TranslationText[] $texts
     *
     * @throws MicrosoftTranslatorExceptionInterface
     */
    public function translate(
        array $texts,
        string $to,
        ?string $from = null,
        ?string $suggestedFrom = null,
        TranslationType $textType = TranslationType::Plain,
        ?string $category = null,
        ProfanityAction $profanityAction = ProfanityAction::NoAction,
        ProfanityMarker $profanityMarker = ProfanityMarker::Asterisk,
        bool $includeAlignment = false,
        bool $includeSentenceLength = false,
        ?string $fromScript = null,
        ?string $toScript = null,
        bool $allowFallback = true,
        ?string $clientTraceId = null,
    ): TranslateResponse {
        $response = $this->sendRequest(
            method: 'POST',
            url: $this->makeUri('translate', [
                'to' => $to,
                'from' => $from,
                'textType' => $textType?->value,
                'category' => $category,
                'profanityAction' => $profanityAction?->value,
                'profanityMarker' => $profanityMarker?->value,
                'includeAlignment' => $includeAlignment ? 'true' : null,
                'includeSentenceLength' => $includeSentenceLength ? 'true' : null,
                'suggestedFrom' => $suggestedFrom,
                'fromScript' => $fromScript,
                'toScript' => $toScript,
                'allowFallback' => $allowFallback ? 'true' : null,
                'api-version' => '3.0',
            ]),
            headers: $this->makeHeaders(['X-ClientTraceId' => $clientTraceId]),
            data: array_map(static fn (TranslationText $text) => ['Text' => $text->text], $texts),
        );

        try {
            return new TranslateResponse(
                translations: array_map(static fn (array $translation) => new Translation(
                    to: $translation['to'],
                    text: $translation['text'],
                    transliteration: isset($translation['transliteration']) ? new Transliteration(
                        script: $translation['transliteration']['script'],
                        text: $translation['transliteration']['text'],
                        alignment: $translation['transliteration']['alignment'],
                        sentLen: $translation['transliteration']['sentLen'],
                    ) : null,
                ), $response['translations'] ?? []),
                detectedLanguage: $response['detectedLanguage'] ?? $from ?? $suggestedFrom,
            );
        } catch (Throwable $exception) {
            throw new InvalidResponseException('Structure error', previous: $exception);
        }
    }

    /**
     * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-languages
     *
     * @param Scope[] $scope
     *
     * @throws MicrosoftTranslatorExceptionInterface
     */
    public function languages(
        ?array $scope = null,
        ?string $acceptLanguage = null,
        ?string $clientTraceId = null,
    ): LanguageListResponse {
        $response = $this->sendRequest(
            method: 'GET',
            url: $this->makeUri('languages', [
                'scope' => $scope === null ? null : implode(',', array_column($scope, 'value')),
                'api-version' => '3.0',
            ]),
            headers: $this->makeHeaders(['X-ClientTraceId' => $clientTraceId, 'Accept-Language' => $acceptLanguage]),
        );

        try {
            return new LanguageListResponse(
                translation: isset($response['translation'])
                    ? array_map(static fn (array $translation) => new LanguageTranslation(
                        name: $translation['name'],
                        nativeName: $translation['nativeName'],
                        direction: LanguageDirection::from($translation['dir']),
                    ), $response['translation'])
                    : null,
                transliteration: isset($response['transliteration'])
                    ? array_map(static fn (array $transliteration) => new LanguageTransliteration(
                        name: $transliteration['name'],
                        nativeName: $transliteration['nativeName'],
                        scripts: $this->parseScripts($transliteration['scripts']),
                    ), $response['translation'])
                    : null,
                dictionary: isset($response['dictionary'])
                    ? array_map(static fn (array $dictionary) => new Dictionary(
                        dictionary: new LanguageTranslation(
                            name: $dictionary['name'],
                            nativeName: $dictionary['nativeName'],
                            direction: LanguageDirection::from($dictionary['dir']),
                        ),
                        translations: $this->parseToScripts($dictionary['translations'] ?? []),
                    ), $response['dictionary'])
                    : null,
            );
        } catch (Throwable $exception) {
            throw new InvalidResponseException('Structure error', previous: $exception);
        }
    }

    /**
     * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-transliterate
     *
     * @param TranslationText[] $texts
     *
     * @return Transliteration[]
     *
     * @throws MicrosoftTranslatorExceptionInterface
     */
    public function transliterate(
        array $texts,
        string $language,
        string $fromScript,
        string $toScript,
        ?string $clientTraceId = null,
    ): array {
        $response = $this->sendRequest(
            method: 'POST',
            url: $this->makeUri('transliterate', [
                'language' => $language,
                'fromScript' => $fromScript,
                'toScript' => $toScript,
            ]),
            headers: $this->makeHeaders(['X-ClientTraceId' => $clientTraceId]),
            data: array_map(fn(TranslationText $text) => ['Text' => $text->text], $texts),
        );

        try {
            return array_map(
                static fn (array $item) => new Transliteration(
                    script: $response['script'],
                    text: $response['text'],
                ),
                $response,
            );
        } catch (Throwable $exception) {
            throw new InvalidResponseException('Structure error', previous: $exception);
        }
    }

    /**
     * @link https://docs.microsoft.com/en-us/azure/cognitive-services/translator/reference/v3-0-detect
     *
     * @param TranslationText[] $texts
     *
     * @return DetectLanguage[]
     *
     * @throws MicrosoftTranslatorExceptionInterface
     */
    public function detect(array $texts, ?string $clientTraceId = null): array
    {
        $response = $this->sendRequest(
            method: 'POST',
            url: $this->makeUri('detect'),
            headers: $this->makeHeaders(['X-ClientTraceId' => $clientTraceId]),
            data: array_map(static fn (TranslationText $text) => ['Text' => $text->text], $texts),
        );

        try {
            return array_map(static fn(array $item) => new DetectLanguage(
                detect: new Detect(
                    language: $item['language'],
                    score: $item['score'],
                    isTranslationSupported: $item['isTranslationSupported'],
                    isTransliterationSupported: $item['isTransliterationSupported'],
                ),
                alternatives: array_map(static fn(array $alternative) => new Detect(
                    language: $alternative['language'],
                    score: $alternative['score'],
                    isTranslationSupported: $alternative['isTranslationSupported'],
                    isTransliterationSupported: $alternative['isTransliterationSupported'],
                ), $item['alternatives'] ?? []),
            ), $response);
        } catch (Throwable $exception) {
            throw new InvalidResponseException('Structure error', previous: $exception);
        }
    }

    /**
     * @throws MicrosoftTranslatorExceptionInterface
     */
    private function sendRequest(string $method, string $url, array $headers, array $data = []): array
    {
        $body = $data === [] ? null : json_encode($data);

        try {
            $response = $this->client->sendRequest(method: $method, uri: $url, body: $body, headers: $headers);
        } catch (NetworkExceptionInterface $exception) {
            throw new NetworkException('Network failure', previous: $exception);
        } catch (RequestExceptionInterface $exception) {
            throw new RequestException('Request exception', previous: $exception);
        } catch (ClientExceptionInterface $exception) {
            throw new ClientException('Client failed', previous: $exception);
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        
        try {
            $content = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw $statusCode >= 400
                ? $this->handleErrorResponse($statusCode, $body, $exception)
                : new InvalidResponseException('Json error', previous: $exception);
        }

        if ($statusCode >= 400) {
            $this->handleErrorResponse($statusCode, $response->getReasonPhrase(), content: $content);
        }

        if (!is_array($content)) {
            throw new InvalidResponseException('Not array');
        }

        return $content;
    }

    private function makeUri(string $path, array $parameters = []): string
    {
        return $this->apiUrl . '/' . $path . ($parameters === [] ? '' : '?' . http_build_query($parameters));
    }

    private function handleErrorResponse(
        int $code,
        string $message,
        ?Throwable $exception = null,
        mixed $content = null,
    ): MicrosoftTranslatorExceptionInterface {
        $message = $content['error']['message'] ?? $message;

        return match ($code) {
            401 => new AuthenticationException($message, previous: $exception),
            default => new InvalidRequestException($message, previous: $exception),
        };
    }

    private function makeHeaders(array $headers = []): array
    {
        return array_replace(
            [
                'Content-Type' => 'application/json',
                $this->authorization->getHeaderName() => $this->authorization->getValue(),
            ],
            $headers,
        );
    }

    private function parseScripts(array $scripts): array
    {
        $result = [];

        foreach ($scripts as $script) {
            $result[$script['code']] = new Script(
                fromScript: new LanguageTranslation(
                    name: $script['name'],
                    nativeName: $script['nativeName'],
                    direction: LanguageDirection::from($script['dir']),
                ),
                toScripts: $this->parseToScripts($script['toScripts'] ?? []),
            );
        }

        return $result;
    }

    private function parseToScripts(array $toScripts): array
    {
        $result = [];

        foreach ($toScripts as $script) {
            $result[$script['code']] = new LanguageTranslation(
                name: $script['name'],
                nativeName: $script['nativeName'],
                direction: LanguageDirection::from($script['dir']),
            );
        }

        return $result;
    }
}