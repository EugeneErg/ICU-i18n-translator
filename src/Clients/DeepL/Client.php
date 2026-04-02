<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\DeepL;

use EugeneErg\Translate\Clients\Contracts\PsrClient;
use EugeneErg\Translate\Clients\DeepL\Language;
use EugeneErg\Translate\Clients\DeepL\Translation;
use EugeneErg\Translate\Clients\DeepL\Exceptions\AuthorizationException;
use EugeneErg\Translate\Clients\DeepL\Exceptions\ConnectionException;
use EugeneErg\Translate\Clients\DeepL\Exceptions\DeepLClientExceptionInterface;
use EugeneErg\Translate\Clients\DeepL\Exceptions\QuotaExceededException;
use EugeneErg\Translate\Clients\DeepL\Exceptions\RequestException;
use EugeneErg\Translate\Clients\DeepL\Exceptions\ResponseParsingException;
use EugeneErg\Translate\Clients\DeepL\Exceptions\TooManyRequestsException;
use EugeneErg\Translate\Clients\DeepL\ValueObjects\Formality;
use EugeneErg\Translate\Clients\DeepL\ValueObjects\LanguageType;
use EugeneErg\Translate\Clients\DeepL\ValueObjects\ModelType;
use EugeneErg\Translate\Clients\DeepL\ValueObjects\SplitSentences;
use EugeneErg\Translate\Clients\DeepL\ValueObjects\TagHandling;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

readonly class Client
{
    public function __construct(
        private PsrClient $psrClient,
        private string $authKey,
        private string $apiUrl
    ) {
    }

    /**
     * Translates text to the specified target language.
     *
     * @link https://developers.deepl.com/docs/api-reference/translate
     *
     * @throws DeepLClientExceptionInterface
     *
     * @return Translation[]
     */
    public function translate(
        array $text,
        string $targetLang,
        ?string $sourceLang = null,
        ?string $context = null,
        ?ModelType $modelType = null,
        ?SplitSentences $splitSentences = null,
        bool $preserveFormatting = false,
        ?Formality $formality = null,
        ?string $glossaryId = null,
        bool $showBilledCharacters = false,
        ?TagHandling $tagHandling = null,
        bool $outlineDetection = true,
        ?array $nonSplittingTags = null,
        ?array $splittingTags = null,
        ?array $ignoreTags = null,
    ): array {
        $data = array_filter([
            'text' => $text,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'context' => $context,
            'model_type' => $modelType,
            'split_sentences' => $splitSentences,
            'preserve_formatting' => $preserveFormatting ? '1' : '0',
            'formality' => $formality,
            'glossary_id' => $glossaryId,
            'show_billed_characters' => $showBilledCharacters ? '1' : '0',
            'tag_handling' => $tagHandling,
            'outline_detection' => $outlineDetection ? '1' : '0',
            'non_splitting_tags' => $nonSplittingTags,
            'splitting_tags' => $splittingTags,
            'ignore_tags' => $ignoreTags,
        ]);
        $url = $this->makeUrl('translate');
        $response = $this->sendRequest('POST', $url, $data, $this->maheHeaders());

        return array_map(
            static fn(array $item) => new Translation(
                detectedSourceLanguage: $item['detected_source_language'],
                text: $item['text'],
            ),
            $response['translations'],
        );
    }

    /**
     * Retrieves available source or target languages.
     *
     * @link https://developers.deepl.com/docs/api-reference/languages
     *
     * @return  Language[]
     *
     * @throws DeepLClientExceptionInterface
     */
    public function getLanguages(LanguageType $type): array
    {
        $url = $this->makeUrl('languages', ['type' => $type->value]);
        $response = $this->sendRequest('GET', $url, $this->maheHeaders());

        return array_map(
            static fn(array $item) => new Language(
                language: $item['language'],
                name: $item['name'],
                supportsFormality: $item['supports_formality'] ?? false,
            ),
            $response,
        );
    }

    /**
     * @throws DeepLClientExceptionInterface
     */
    private function sendRequest(string $method, string $url, array $headers, ?array $data = null): array
    {
        try {
            $response = $this->psrClient->sendRequest(
                method: $method,
                uri: $url,
                body: json_encode($data, flags: JSON_THROW_ON_ERROR),
                headers: $headers,
            );

            return $this->parseResponse($response);
        } catch (ClientExceptionInterface $e) {
            throw new ConnectionException('Failed to connect to DeepL API: ' . $e->getMessage(), 0, $e);
        } catch (JsonException $e) {
            throw new ResponseParsingException('Failed to parse response: ' . $e->getMessage(), 0, $e);
        }
    }

    private function makeUrl(string $path, array $parameters = []): string
    {
        return $this->apiUrl . '/' . $path . ($parameters === [] ? '' : '?' . http_build_query($parameters));
    }

    private function maheHeaders(array $headers = []): array
    {
        return array_merge([
            'Authorization' => 'DeepL-Auth-Key ' . $this->authKey,
            'Content-Type' => 'application/json',
        ], $headers);
    }

    /**
     * @throws ResponseParsingException
     * @throws AuthorizationException
     * @throws QuotaExceededException
     * @throws TooManyRequestsException
     * @throws RequestException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ResponseParsingException('Invalid JSON response', 0, $e);
        }

        if ($statusCode >= 400) {
            $message = $data['message'] ?? 'Unknown error';
            throw match ($statusCode) {
                403 => new AuthorizationException($message),
                429 => new TooManyRequestsException($message),
                456 => new QuotaExceededException($message),
                default => new RequestException($message),
            };
        }

        return $data;
    }
}
