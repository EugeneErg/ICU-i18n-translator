# ICU i18n Translator

A PHP library for **automatic machine translation of ICU Message Format strings** with persistent caching. Feed it plurals, selects, and complex ICU patterns — it translates every variant individually, preserves all formatting variables, and caches results in your database so the API is called only once per unique string.

```bash
composer require eugene-erg/icu-i18n-translator
```

---

## Why this library?

Most PHP i18n solutions require you to **write translations manually** for every language. This library is for a different workflow: you write strings in one language and let machine translation APIs (DeepL, Google Translate, etc.) fill in the rest — automatically, with full ICU format support.

### How it differs from alternatives

| | This library | symfony/translation | php-translation/translator | bermudaphp/polyglot |
|---|---|---|---|---|
| **Auto-translates via API** | ✅ Core feature | ❌ Manual only | ⚠️ Basic fallback | ❌ Manual only |
| **ICU plural/select aware** | ✅ Per-variant | ❌ Whole string | ❌ Whole string | ✅ Formatting only |
| **Preserves ICU variables** | ✅ `{count}`, `{price, number}` safe | N/A | ❌ Can corrupt | N/A |
| **DB caching** | ✅ Built-in | ❌ | ❌ | ❌ |
| **Language auto-detection** | ✅ | ❌ | ❌ | ❌ |
| **Multiple API backends** | ✅ | N/A | ⚠️ Single | N/A |
| **File import/export** | ✅ | ✅ | ✅ | ✅ |
| **Framework** | Agnostic | Symfony | Symfony | Agnostic |

**The key difference:** when you call `translateMessage('{count, plural, one {# item} other {# items}}', ['count' => 5], 'fr')`, this library does not send the raw ICU pattern to the translation API. It splits it into `{count} item` and `{count} items`, translates each independently, and reassembles a valid French ICU pattern. This prevents the API from corrupting `{count, plural, ...}` syntax, which virtually all translation APIs do when given raw ICU strings.

---

## Requirements

- PHP 8.2+
- `ext-intl`
- Your own implementations of the repository interfaces (any storage: MySQL, PostgreSQL, Redis, in-memory)
- Your own implementation of `TranslatorInterface` (wrapping DeepL, Google, OpenAI, etc.)

---

## Core concept

The library manages four tables. You implement how they are stored.

```
translate          — one translated variant
  pattern          "5 éléments"
  locale           "fr"

group              — one ICU message
  original_pattern "{count, plural, one {# item} other {# items}}"
  pattern          "{count, plural, one {0} other {1}}"   ← selector pattern
  locale           "en"
  context          optional hint for the translator ("e-commerce product list")

group_translates   — which translate belongs to which group variant
  group_id, translate_id, key, source_id

path               — node in a localization file tree
  parent_id, group_id, value
```

A `group` never stores translated text directly. It stores the ICU selector and points to `translate` records through `group_translates`. This means the string `{count} item` can be reused across many groups.

---

## Setup

### 1. Implement the repositories (7 interfaces)

```php
// All interfaces are in EugeneErg\IcuI18nTranslator\Repositories\
// Read and Write are separated — you can use different sources for each.

class DoctrineGroupRepository implements
    ReadGroupRepositoryInterface,
    WriteGroupRepositoryInterface
{
    public function findByPattern(string $originalPattern, ?string $context, ?string $locale = null): ?Group
    {
        // SELECT ... WHERE original_pattern = ? AND context <=> ? AND (locale = ? OR ? IS NULL)
    }

    public function create(string $originalPattern, string $pattern, ?string $context, string $locale): Group
    {
        // INSERT INTO groups ...
        return new Group(new GroupId($id), $originalPattern, $pattern, $locale, $context);
    }

    // ... find(), list(), delete()
}
```

### 2. Implement TranslatorInterface

```php
use EugeneErg\IcuI18nTranslator\TranslatorInterface;
use EugeneErg\IcuI18nTranslator\DataTransferObjects\Variable;
use EugeneErg\IcuI18nTranslator\ValueObjects\Translated;
use DeepL\Translator as DeepLClient;

class DeepLTranslator implements TranslatorInterface
{
    public function __construct(private DeepLClient $client) {}

    /**
     * $pattern is a mixed array of plain strings and Variable objects.
     * Variables represent ICU placeholders like {count} or {price, number, currency}.
     * YOU MUST NOT translate Variable objects — pass them through unchanged.
     */
    public function translate(array $pattern, string $fromLocale, string $toLocale, ?string $context = null): array
    {
        // Encode Variables as XML tags that DeepL will leave untouched
        $text = '';
        foreach ($pattern as $part) {
            $text .= $part instanceof Variable
                ? "<keep id=\"{$part->value}\"/>"
                : $part;
        }

        $result = $this->client->translateText(
            $text,
            $fromLocale,
            $toLocale,
            ['tag_handling' => 'xml', 'ignore_tags' => ['keep'], 'context' => $context]
        );

        // Decode XML tags back to Variable objects
        return $this->decode($result->text, $pattern);
    }

    public function translateWithDetect(array $pattern, string $toLocale, ?string $context = null): Translated
    {
        // Same encoding, but fromLocale is null — API detects it
        $result = $this->client->translateText(..., null, $toLocale, [...]);
        return new Translated(
            locale: strtolower($result->detectedSourceLang),
            pattern: $this->decode($result->text, $pattern),
        );
    }

    public function canTranslate(string $toLocale, ?string $fromLocale = null): bool
    {
        $supported = ['en', 'de', 'fr', 'es', 'it', 'nl', 'pl', 'ru', 'ja', 'zh'];
        return in_array(strtolower($toLocale), $supported, true)
            && ($fromLocale === null || in_array(strtolower($fromLocale), $supported, true));
    }

    private function decode(string $text, array $originalPattern): array
    {
        $variables = array_filter($originalPattern, fn($p) => $p instanceof Variable);
        $parts = preg_split('/(<keep id="\d+"\/> ?)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = [];
        foreach ($parts as $part) {
            if (preg_match('/<keep id="(\d+)"\/>/', $part, $m)) {
                $result[] = $variables[(int)$m[1]];
            } elseif ($part !== '') {
                $result[] = $part;
            }
        }
        return $result;
    }
}
```

### 3. Implement FormatterInterface (for file import/export)

```php
use EugeneErg\IcuI18nTranslator\FormatterInterface;
use EugeneErg\IcuI18nTranslator\DataTransferObjects\FilePathContainer;

class JsonFormatter implements FormatterInterface
{
    public function parse(string $content): FilePathContainer
    {
        return $this->toContainer(json_decode($content, true, flags: JSON_THROW_ON_ERROR));
    }

    public function format(FilePathContainer $file): string
    {
        return json_encode(
            $this->fromContainer($file),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    private function toContainer(array $data): FilePathContainer
    {
        $children = [];
        foreach ($data as $key => $value) {
            $children[$key] = is_array($value) ? $this->toContainer($value) : (string)$value;
        }
        return new FilePathContainer($children);
    }

    private function fromContainer(FilePathContainer $container): array
    {
        $result = [];
        foreach ($container->children as $key => $child) {
            $result[$key] = $child instanceof FilePathContainer
                ? $this->fromContainer($child)
                : (string)$child;
        }
        return $result;
    }
}
```

### 4. Wire everything together

```php
use EugeneErg\IcuI18nTranslator\Translator;
use EugeneErg\ICUMessageFormatParser\Parser;

$translator = new Translator(
    readGroupRepository:           $readGroupRepo,
    writeGroupRepository:          $writeGroupRepo,
    readTranslateRepository:       $readTranslateRepo,
    writeTranslateRepository:      $writeTranslateRepo,
    writeGroupTranslateRepository: $writeGroupTranslateRepo,
    readPathRepository:            $readPathRepo,
    writePathRepository:           $writePathRepo,
    parser:                        new Parser(),
    translators:                   [new DeepLTranslator($deepLClient)],
    formatters:                    ['json' => new JsonFormatter()],
);
```

---

## Usage

### Translate plain text

```php
// Source language known
$result = $translator->translateText('Hello world', toLocale: 'fr', fromLocale: 'en');
// → 'Bonjour monde'

// Source language unknown — detected by the API
$result = $translator->translateText('Hello world', toLocale: 'de');
// → 'Hallo Welt'

// With context (improves translation quality for ambiguous terms)
$result = $translator->translateText('File', toLocale: 'fr', fromLocale: 'en', context: 'computer menu item');
// → 'Fichier'  (not 'Dossier' or 'Déposer')
```

### Translate ICU plural

```php
$pattern = '{count, plural, one {# item} other {# items}}';

echo $translator->translateMessage($pattern, ['count' => 1], 'fr', 'en');
// → '1 élément'

echo $translator->translateMessage($pattern, ['count' => 5], 'fr', 'en');
// → '5 éléments'
```

What happens internally:
1. Parser produces selector `{count, plural, one {0} other {1}}` and variants `{count} item`, `{count} items`
2. Each variant is translated independently — the `{count}` variable placeholder is protected from the API
3. Results are cached in DB — the next call for the same pattern+locale costs zero API calls
4. The ICU pattern is reconstructed from translated variants and formatted with `count = 5`

### Translate ICU select

```php
$pattern = '{gender, select, male {He sent} female {She sent} other {They sent}} a message';

echo $translator->translateMessage($pattern, ['gender' => 'female'], 'de', 'en');
// → 'Sie hat eine Nachricht gesendet'
```

### Translate a string with formatting variables

```php
// {price, number, currency} is an ICU variable — the library passes it through untranslated
$pattern = 'Your order of {count, plural, one {# item} other {# items}} totals {total, number, currency}';

echo $translator->translateMessage(
    $pattern,
    ['count' => 3, 'total' => 59.99],
    'fr',
    'en',
);
// → 'Votre commande de 3 articles totalise 59,99 €'
```

### Import a localization file

```php
$json = file_get_contents('translations/messages.en.json');
// {
//   "auth": {
//     "login": "Log in",
//     "errors": {
//       "invalid": "Invalid email or password"
//     }
//   },
//   "cart": {
//     "items": "{count, plural, one {# item in cart} other {# items in cart}}"
//   }
// }

$translator->addFile('json', 'messages', $json, locale: 'en');
// All strings are now registered in the DB.
// No translations are triggered yet.
```

### Export a translated file

```php
// This triggers translation for any strings not yet in DB for locale 'fr'
$translated = $translator->getFile('json', 'messages', locale: 'fr');
file_put_contents('translations/messages.fr.json', $translated);
// {
//   "auth": {
//     "login": "Se connecter",
//     "errors": {
//       "invalid": "Email ou mot de passe invalide"
//     }
//   },
//   "cart": {
//     "items": "{count, plural, one {# article dans le panier} other {# articles dans le panier}}"
//   }
// }
```

### Browse and edit translations

```php
// List registered string groups (paginated)
$groups = $translator->getGroups(pageSize: 50, page: 1);

foreach ($groups as $group) {
    echo $group->originalPattern . ' [' . $group->locale . "]\n";
}

// Inspect all variants of one group with their current translations
$translates = $translator->getTranslates($group->id, locale: 'fr');
// [
//   '0' => Translate(cases: ['plural' => ['count' => 'one']], pattern: '{count} élément'),
//   '1' => Translate(cases: ['plural' => ['count' => null]],  pattern: null),  // missing
// ]

// Fix a missing translation manually
$translator->setTranslate($group->id, key: '1', locale: 'fr', pattern: '{count} éléments');

// Remove a translation (e.g., to force re-translation)
$translator->deleteTranslateFromGroup($group->id, key: '0', locale: 'fr');
```

### Multiple translation backends

If you have multiple API keys or want a fallback chain, register several translators. The first one whose `canTranslate()` returns `true` is used:

```php
$translator = new Translator(
    // ...
    translators: [
        new DeepLTranslator($deepLClient),    // preferred
        new GoogleTranslator($googleClient),  // fallback
    ],
);
```

---

## Exceptions

All exceptions implement `TranslatorExceptionInterface`:

| Exception | When |
|---|---|
| `FormatNotFoundException` | Unknown file format key passed to `addFile()`/`getFile()` |
| `FileNotFoundException` | File not found in DB when calling `getFile()` |
| `GroupNotFoundException` | Group not found when calling `getTranslates()` |
| `IncorrectTransferPatternException` | Invalid ICU pattern or `setTranslate()` called with a multi-variant pattern |
| `UnexpectedTranslateDirectionException` | No registered translator supports the requested locale pair |

```php
use EugeneErg\IcuI18nTranslator\Exceptions\TranslatorExceptionInterface;

try {
    $result = $translator->translateText('Hello', toLocale: 'klingon', fromLocale: 'en');
} catch (TranslatorExceptionInterface $e) {
    // handle
}
```

---

## Architecture

```
Translator
├── TranslatorInterface[]          external API adapters (DeepL, Google, OpenAI, …)
├── FormatterInterface[]           file format adapters (JSON, YAML, PHP array, …)
│
├── ReadGroupRepositoryInterface   \
├── WriteGroupRepositoryInterface  |
├── ReadTranslateRepositoryInterface   > implement these 7 for your storage layer
├── WriteTranslateRepositoryInterface  |
├── WriteGroupTranslateRepositoryInterface
├── ReadPathRepositoryInterface    |
└── WritePathRepositoryInterface   /
```

Read and Write repositories are separate interfaces. This lets you point reads at a read replica and writes at a primary, or use a cache layer for reads only.

---

## Static analysis & tests

```bash
./vendor/bin/phpstan analyse   # level: max with bleedingEdge
./vendor/bin/phpunit
```

---

## License

MIT