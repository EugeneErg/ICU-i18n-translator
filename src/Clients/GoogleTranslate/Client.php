<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\GoogleTranslate;

use EugeneErg\Translate\Clients\Contracts\PsrClient;
use EugeneErg\Translate\Clients\GoogleTranslate\DetectLanguage;
use EugeneErg\Translate\Clients\GoogleTranslate\DetectLanguageResponse;
use EugeneErg\Translate\Clients\GoogleTranslate\LocationModel;
use EugeneErg\Translate\Clients\GoogleTranslate\LocationModelGlossary;
use EugeneErg\Translate\Clients\GoogleTranslate\Romanization;
use EugeneErg\Translate\Clients\GoogleTranslate\RomanizeTextResponse;
use EugeneErg\Translate\Clients\GoogleTranslate\SupportedLanguage;
use EugeneErg\Translate\Clients\GoogleTranslate\SupportedLanguages;
use EugeneErg\Translate\Clients\GoogleTranslate\TranslateTextResponse;
use EugeneErg\Translate\Clients\GoogleTranslate\Translation;
use EugeneErg\Translate\Clients\GoogleTranslate\TransliterationConfig;
use EugeneErg\Translate\Clients\GoogleTranslate\ValueObjects\MimeType;
use Psr\Http\Message\ResponseInterface;

readonly class Client
{
    public function __construct(
        private PsrClient $psrClient,
        private string $apiUrl,
        private string $parentNumberOrId,
    ) {
    }

    /**
     * Translates input text to the target language.
     *
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/projects/translateText
     *
     * @throws GoogleTranslateClientExceptionInterface
     */
    public function translateText(
        array $contents,
        string $targetLanguageCode,
        ?string $sourceLanguageCode = null,
        ?MimeType $mimeType = null,
        ?TransliterationConfig $transliterationConfig = null,
        array $labels = [],
        ?LocationModelGlossary $locationModelGlossary = null,
    ): TranslateTextResponse {
        $response = $this->sendRequest(
            'POST',
            $this->getUrl(($locationModelGlossary?->location === null ? '' : '/locations/' . $locationModelGlossary?->location) . ':translateText'),
            array_filter([
                'contents' => $contents,
                'targetLanguageCode' => $targetLanguageCode,
                'sourceLanguageCode' => $sourceLanguageCode,
                'mimeType' => $mimeType,
                'model' => $locationModelGlossary?->model,
                'glossaryConfig' => $locationModelGlossary?->glossaryConfig,
                'transliterationConfig' => $transliterationConfig,
                'labels' => $labels,
            ], static fn (mixed $value) => $value !== null),
        );

        return new TranslateTextResponse(
            translations: array_map(
                static fn (array $item) => new Translation(
                    translatedText: $item['translatedText'],
                    model: $item['model'] ?? null,
                    detectedLanguageCode: $item['detectedSourceLanguage'] ?? $sourceLanguageCode,
                ),
                $response['translations'] ?? [],
            ),
            glossaryTranslations: array_map(
                static fn (array $item) => new Translation(
                    translatedText: $item['translatedText'],
                    model: $item['model'] ?? null,
                    detectedLanguageCode: $item['detectedSourceLanguage'] ?? $sourceLanguageCode,
                ),
                $response['translations'] ?? [],
            ),
        );
    }

    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/projects/romanizeText
     *
     * @param string[] $contents
     */
    public function romanizeText(
        array $contents,
        ?string $sourceLanguageCode = null,
        ?string $location = null,
    ): RomanizeTextResponse {
        $response = $this->sendRequest(
            'POST',
            $this->getUrl(($location === null ? '' : '/locations/' . $location) . ':romanizeText'),
            array_filter([
                'contents' => $contents,
                'sourceLanguageCode' => $sourceLanguageCode,
            ], static fn (mixed $value) => $value !== null),
        );

        return new RomanizeTextResponse(
            romanizations: array_map(
                static fn (array $item) => new Romanization(
                    romanizedText: $item['romanizedText'],
                    detectedLanguageCode: $item['detectedSourceLanguage'] ?? $sourceLanguageCode,
                ),
                $response['romanizations'] ?? [],
            ),
        );
    }

    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/projects/detectLanguage
     *
     * @param string[] $labels
     */
    public function detectLanguage(
        string $content,
        ?MimeType $mimeType = null,
        array $labels = [],
        ?LocationModel $locationModel = null,
    ): DetectLanguageResponse {
        $response = $this->sendRequest(
            'POST',
            $this->getUrl(($locationModel?->location === null ? '' : '/locations/' . $locationModel?->location) . ':detectLanguage'),
            array_filter([
                'content' => $content,
                'model' => $locationModel?->model,
                'mimeType' => $mimeType,
                'labels' => $labels,
            ], static fn (mixed $value) => $value !== null),
        );

        return new DetectLanguageResponse(
            languages: array_map(
                static fn (array $item) => new DetectLanguage(
                    languageCode: $item['languageCode'],
                    confidence: $item['confidence'],
                ),
                $response['languages'] ?? [],
            ),
        );
    }

    /**
     * @link https://cloud.google.com/translate/docs/reference/rest/v3/projects/getSupportedLanguages
     */
    public function getSupportedLanguages(
        ?string $displayLanguageCode = null,
        ?LocationModel $locationModel = null,
    ): SupportedLanguages {
        $response = $this->sendRequest(
            'POST',
            $this->getUrl(($locationModel?->location === null ? '' : '/locations/' . $locationModel?->location) . '/supportedLanguages'),
            array_filter([
                'displayLanguageCode' => $displayLanguageCode,
                'model' => $locationModel?->model,
            ], static fn (mixed $value) => $value !== null),
        );

        return new SupportedLanguages(
            languages: array_map(
                static fn (array $item) => new SupportedLanguage(
                    languageCode: $item['languageCode'],
                    displayName: $item['displayName'],
                    supportSource: $item['supportSource'],
                    supportTarget: $item['supportTarget'],
                ),
                $response['languages'] ?? [],
            ),
        );
    }

    /**
     * Sends HTTP request to the Google Translate API.
     *
     * @throws GoogleTranslateClientExceptionInterface
     */
    private function sendRequest(string $method, string $url, array $data = [], array $headers = []): array
    {
        try {
            $response = $this->psrClient->sendRequest(
                method: $method,
                uri: $url,
                data: $data === [] ? null : json_encode($data, flags: JSON_THROW_ON_ERROR),
                headers: $headers,
            );
        } catch (\Exception $e) {
            throw new RequestException('Failed to send request: ' . $e->getMessage(), 0, $e);
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $this->handleErrorResponse($response);
        }

        $content = $response->getBody()->getContents();
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RequestException('Invalid JSON response: ' . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Builds the full URL for the API request.
     */
    private function getUrl(string $path, array $parameters = []): string
    {
        return $this->apiUrl
            . '/v3/'
            . $this->parentNumberOrId
            . $path
            . ($parameters === [] ? '' : '?' . http_build_query($parameters));
    }

    /**
     * Handles error responses from the API.
     *
     * @throws GoogleTranslateClientExceptionInterface
     */
    private function handleErrorResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        $errorData = json_decode($content, true);

        $message = $errorData['error']['message'] ?? 'Unknown error';

        switch ($statusCode) {
            case 401:
            case 403:
                throw new AuthenticationException($message);
            case 429:
                throw new QuotaExceededException($message);
            default:
                throw new RequestException($message);
        }
    }

    private function makeHeaders(array $headers = []): array
    {
        return array_merge(['Content-Type' => 'application/json', 'Accept' => 'application/json'], $headers);
    }
}