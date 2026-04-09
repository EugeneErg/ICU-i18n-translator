<?php

declare(strict_types = 1);

namespace EugeneErg\Translate;

use EugeneErg\ICUMessageFormatParser\DataTransferObjects\Cases;
use EugeneErg\ICUMessageFormatParser\DataTransferObjects\Contracts\ICUTypeMergeInterface;
use EugeneErg\ICUMessageFormatParser\DataTransferObjects\Types;
use EugeneErg\ICUMessageFormatParser\Parser;
use EugeneErg\ICUMessageFormatParser\Variator;
use EugeneErg\Translate\DataTransferObjects\FilePathContainer;
use EugeneErg\Translate\DataTransferObjects\Variable;
use EugeneErg\Translate\Entities\Path;
use EugeneErg\Translate\Entities\Translate;
use EugeneErg\Translate\Exceptions\TranslateException;
use EugeneErg\Translate\Formatters\FormatterInterface;
use EugeneErg\Translate\Repositories\ReadGroupRepositoryInterface;
use EugeneErg\Translate\Repositories\ReadPathRepositoryInterface;
use EugeneErg\Translate\Repositories\ReadTranslateRepositoryInterface;
use EugeneErg\Translate\Repositories\WriteGroupRepositoryInterface;
use EugeneErg\Translate\Repositories\WriteGroupTranslateRepositoryInterface;
use EugeneErg\Translate\Repositories\WritePathRepositoryInterface;
use EugeneErg\Translate\Repositories\WriteTranslateRepositoryInterface;
use EugeneErg\Translate\Translators\Contracts\TranslatorInterface;
use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\PathId;
use MessageFormatter;

/**
 * //todo три типа перевода:
 * //todo 1) перевод простого текста (например пользовательского сообщения от юзера к юзеру)
 * //todo 2) перевод icu message (например форматированная ошибка и т д)
 * //todo 3) перевод файла (файл будет отдаваться в различных фораматах на фронт или мобилку и т д)
 */
readonly class Translator
{
    /**
     * @param TranslatorInterface[] $translators
     * @param FormatterInterface[] $formatters
     */
    public function __construct(
        private ReadGroupRepositoryInterface $readGroupRepository,
        private WriteGroupRepositoryInterface $writeGroupRepository,
        private ReadTranslateRepositoryInterface $readTranslateRepository,
        private WriteTranslateRepositoryInterface $writeTranslateRepository,
        private WriteGroupTranslateRepositoryInterface $writeGroupTranslateRepository,
        private ReadPathRepositoryInterface $readPathRepository,
        private WritePathRepositoryInterface $writePathRepository,
        private Parser $parser,
        private Variator $variator,
        private array $translators,
        private array $formatters,
    ) {
    }

    /**
     * @description переводит пользовательский текст
     *
     * @throws TranslateException
     */
    public function translateText(
        string $text,
        string $toLocale,
        ?string $fromLocale = null,
        ?string $context = null,
    ): string {
        return $this->translateMessage(
            pattern: $this->parser->quote($text),
            values: [],
            toLocale: $toLocale,
            fromLocale: $fromLocale,
            context: $context,
        );
    }

    /**
     * @throws TranslateException
     */
    public function translateMessage(
        string $pattern,
        array $values,
        string $toLocale,
        ?string $fromLocale = null,
        ?string $context = null,
    ): string {
        $types = $this->parser->parse($pattern);
        $cases = $this->variator->typesToCases($types);
        $key = $this->messageFormat(locale: $toLocale, pattern: (string) $cases->variator, values: $values);
        $groupOriginalPattern = (string) $types;
        $groupPattern = (string) $cases->variator;
        $group = $this->readGroupRepository->findByPattern(originalPattern: $groupOriginalPattern, context: $context, locale: $fromLocale);
        //если группа найдена, а исходный язык не задан, берем из группы
        $fromLocale ??= $group->locale ?? null;
        $variant = $cases->types[$key];
        $variantPattern = (string) $variant;

        //если исходный язык неизвестен, значит и группа не найдена
        if ($fromLocale === null) {
            $sourceTranslate = $this->readTranslateRepository->find(pattern: $variantPattern);

            if ($sourceTranslate === null) {
                //если в базе нет такой строки, значит исходный язык можем узнать только из внешних сервисов. Там и переведем
                return $this->translateMessageWithoutOriginalLanguage(
                    originalPattern: $groupOriginalPattern,
                    groupPattern: $groupPattern,
                    context: $context,
                    variant: $variant,
                    variantPattern: $variantPattern,
                    values: $values,
                    toLocale: $toLocale,
                    key: $key,
                );
            }

            //если в базе есть такая строка, берем из неё исходный язык
            $fromLocale = $sourceTranslate->locale;
            $originalGroupTranslateIsEmpty = true;
        } elseif ($group !== null) {
            //если исходный язык задан и есть группа, то возможно есть и перевод
            $result = $this->readTranslateRepository->findByGroup(groupId: $group->id, key: $key, locale: $toLocale);

            if ($result !== null) {
                // если перевод есть, возвращаем его
                return $this->messageFormat(locale: $toLocale, pattern: $result->pattern, values: $values);
            }

            $sourceTranslate = $this->readTranslateRepository->findByGroup(groupId: $group->id, key: $key, locale: $fromLocale);
            $originalGroupTranslateIsEmpty = $sourceTranslate === null;
        } else {
            $originalGroupTranslateIsEmpty = true;
        }

        $group ??= $this->writeGroupRepository->create(
            originalPattern: $groupOriginalPattern,
            pattern: $groupPattern,
            context: $context,
            locale: $fromLocale,
        );
        $sourceTranslate ??= $this->writeTranslateRepository->create(pattern: $variantPattern, locale: $fromLocale);

        if ($originalGroupTranslateIsEmpty) {
            $this->writeGroupTranslateRepository->create(
                groupId: $group->id,
                translateId: $sourceTranslate->id,
                key: $key,
            );
        }

        $translate = $this->translate(
            variant: $variant,
            fromLocale: $fromLocale,
            toLocale: $toLocale,
            key: $key,
            context: $context,
            groupId: $group->id,
            sourceId: $sourceTranslate->id,
        );

        return $this->messageFormat($toLocale, $translate->pattern, $values);
    }

    /**
     * @throws TranslateException
     */
    public function addFile(string $format, string $name, string $content, string $locale, string $context = null): void
    {
        if (!isset($this->formatters[$format])) {
            throw new TranslateException('Format not found.');
        }

        $file = $this->formatters[$format]->parse($content);
        $this->saveFile($file, $name, $locale, $context);
    }

    /**
     * @throws TranslateException
     */
    public function getFile(string $format, string $name, string $locale): string
    {
        if (!isset($this->formatters[$format])) {
            throw new TranslateException('Format not found.');
        }

        $file = $this->loadFile($name, $locale);

        if ($file === null) {
            throw new TranslateException('File not found.');
        }

        return $this->formatters[$format]->format($file);
    }

    /**
     * @throws TranslateException
     */
    private function messageFormat(string $locale, string $pattern, array $values): string
    {
        $messageFormater = MessageFormatter::create(locale: $locale, pattern: $pattern);
        $result = $messageFormater->format(values: $values);

        if ($result === false) {
            throw new TranslateException(message: $messageFormater->getErrorMessage(), code: $messageFormater->getErrorCode());
        }

        return $result;
    }

    /**
     * @throws TranslateException
     */
    private function translateMessageWithoutOriginalLanguage(
        string $originalPattern,
        string $groupPattern,
        ?string $context,
        Types $variant,
        string $variantPattern,
        array $values,
        string $toLocale,
        string $key,
    ): string {
        foreach ($this->translators as $translator) {
            if (!$translator->canTranslate($toLocale)) {
                continue;
            }

            $translatedVariant = $this->prepare($variant, function (array $pattern) use (
                $translator,
                &$fromLocale,
                $context,
                $toLocale,
            ) {
                $result = $translator->translateWithDetect(
                    pattern: $pattern,
                    toLocale: $toLocale,
                    context: $context,
                );
                $fromLocale = $result->locale;

                return $result->pattern;
            });
            $group = $this->writeGroupRepository->create(
                originalPattern: $originalPattern,
                pattern: $groupPattern,
                context: $context,
                locale: $fromLocale,
            );
            $sourceTranslate = $this->writeTranslateRepository->create(pattern: $variantPattern, locale: $toLocale);
            $translatedVariantPattern = (string) $translatedVariant;
            $translate = $this->readTranslateRepository->find(pattern: $translatedVariantPattern, locale: $toLocale)
                ?? $this->writeTranslateRepository->create(
                    pattern: $translatedVariantPattern,
                    locale: $toLocale,
                );
            $this->writeGroupTranslateRepository->create(
                groupId: $group->id,
                translateId: $translate->id,
                key: $key,
                sourceId: $sourceTranslate->id,
            );

            return $this->messageFormat($toLocale, $translatedVariantPattern, $values);
        }

        throw new TranslateException('Unexpected translate direction.');
    }

    /**
     * @throws TranslateException
     */
    private function translate(
        Types $variant,
        string $fromLocale,
        string $toLocale,
        string $key,
        ?string $context,
        GroupId $groupId,
        ValueObjects\TranslateId $sourceId,
    ): Translate {
        foreach ($this->translators as $translator) {
            if (!$translator->canTranslate($toLocale, $fromLocale)) {
                continue;
            }

            $translatedVariant = $this->prepare($variant, fn (array $pattern) => $translator->translate(
                pattern: $pattern,
                fromLocale: $fromLocale,
                toLocale: $toLocale,
                context: $context,
            ));
            $translatedVariantPattern = (string) $translatedVariant;
            $result = $this->readTranslateRepository->find(pattern: $translatedVariantPattern, locale: $toLocale)
                ?? $this->writeTranslateRepository->create(
                    pattern: $translatedVariantPattern,
                    locale: $toLocale,
                );
            $this->writeGroupTranslateRepository->create(
                groupId: $groupId,
                translateId: $result->id,
                key: $key,
                sourceId: $sourceId,
            );

            return $result;
        }

        throw new TranslateException('Unexpected translate direction.');
    }

    /**
     * @throws TranslateException
     */
    private function prepare(Types $types, callable $translate): Types
    {
        $stringAndVariables = [];
        $text = '';
        $variableTypes = [];
        $i = 0;

        foreach ($types as $type) {
            if ($type instanceof ICUTypeMergeInterface) {
                $text .= $this->messageFormat('EN', (string) $type, []);
            } else {
                if ($text !== '') {
                    $stringAndVariables[] = $text;
                    $text = '';
                }

                $stringAndVariables[] = new Variable($i);
                $variableTypes[$i] = $type;
                $i++;
            }
        }

        if ($text !== '') {
            $stringAndVariables[] = $text;
        }

        $stringAndVariables = $translate($stringAndVariables);
        $result = [];

        foreach ($stringAndVariables as $item) {
            if ($item instanceof Variable) {
                $result[] = $variableTypes[$item->value];
            } else {
                array_push($result, ...$this->parser->parse($this->parser->quote($item))->types);
            }
        }

        return new Types($result);
    }

    private function saveFile(
        DataTransferObjects\FilePathContainer $file,
        string $name,
        string $locale,
        ?string $context,
        ?PathId $parentId = null,
    ): void {
        $path = $parentId === null
            ? $this->readPathRepository->findRoot($name)
            : $this->readPathRepository->findChild($name, $parentId);
        $path ??= $this->writePathRepository->create(
            value: $name,
            parentId: $parentId,
        );

        foreach ($file->children as $value => $child) {
            if ($child instanceof FilePathContainer) {
                $this->saveFile($child, (string) $value, $locale, $context, $path->id);
            } elseif ($child instanceof Types) {
                $this->saveGroup($child, (string) $value, $locale, $context, $path->id);
            } else {
                $this->saveGroup($this->parser->parse($this->parser->quote($child)), (string) $value, $locale, $context, $path->id);
            }
        }
    }

    /**
     * @throws TranslateException
     */
    private function loadFile(string $name, string $locale): ?DataTransferObjects\FilePathContainer
    {
        $path = $this->readPathRepository->findRoot($name);

        return $path === null ? null : $this->makeChildren($path->id, $locale);
    }

    /**
     * @throws TranslateException
     */
    private function loadPath(Path $path, string $locale): Types|DataTransferObjects\FilePathContainer
    {
        if ($path->groupId === null) {
            return $this->makeChildren($path->id, $locale);
        }

        $group = $this->readGroupRepository->find($path->groupId);

        if ($group === null) {
            throw new TranslateException('Group not found.');
        }

        $types = $this->parser->parse($group->originalPattern);

        if ($group->locale === $locale) {
            return $types;
        }

        $cases = $this->variator->typesToCases($types);
        $result = $this->readTranslateRepository->groupListByKey($group->id, $locale);
        $needKeys = array_diff(array_keys($cases->types), array_keys($result));
        $sources = $needKeys === []
            ? []
            : $this->readTranslateRepository->keysListByKey($group->id, $group->locale, $needKeys);

        foreach ($cases->types as $key => $variant) {
            if (!isset($result[$key])) {
                if (isset($sources[$key])) {
                    $source = $sources[$key];
                } else {
                    $stringVariant = (string) $variant;
                    $source = $this->readTranslateRepository->find($stringVariant, $group->locale)
                        ?? $this->writeTranslateRepository->create($stringVariant, $group->locale);
                    $this->writeGroupTranslateRepository->create($group->id, $source->id, $key);
                }

                $result[$key] = $this->translate(
                    variant: $variant,
                    fromLocale: $group->locale,
                    toLocale: $locale,
                    key: (string) $key,
                    context: $group->context,
                    groupId: $group->id,
                    sourceId: $source->id,
                );
            }
        }

        $translateVariants = [];

        foreach ($result as $key => $translate) {
            $translateVariants[$key] = $this->parser->parse($translate->pattern);
        }

        return $this->variator->casesToTypes(new Cases(
            types: $translateVariants,
            variator: $cases->variator,
        ));
    }

    /**
     * @throws TranslateException
     */
    private function makeChildren(PathId $pathId, string $locale): DataTransferObjects\FilePathContainer
    {
        $children = [];
        $paths = $this->readPathRepository->listByParentId($pathId);

        foreach ($paths as $path) {
            $children[$path->value] = $this->loadPath($path, $locale);
        }

        return new DataTransferObjects\FilePathContainer(children: $children);
    }

    private function saveGroup(Types $pattern, string $name, string $locale, ?string $context, PathId $parentId): void
    {
        $path = $this->readPathRepository->findChild($name, $parentId);

        if ($path !== null && $path->groupId === null) {
            $this->deleteBranch($path->id);
        }

        if ($path === null) {
            $patternString = (string) $pattern;
            $group = $this->readGroupRepository->findByPattern(originalPattern: $patternString, context: $context, locale: $locale);

            if ($group === null) {
                $cases = $this->variator->typesToCases($pattern);
                $groupPattern = (string) $cases->variator;
                $group = $this->writeGroupRepository->create(
                    originalPattern: $patternString,
                    pattern: $groupPattern,
                    context: $context,
                    locale: $locale,
                );
            }

            $this->writePathRepository->create(
                value: $name,
                parentId: $parentId,
                groupId: $group->id,
            );
        }
    }

    private function deleteBranch(PathId $parentId): void
    {
        $children = $this->readPathRepository->listByParentId($parentId);

        foreach ($children as $child) {
            if ($child->groupId !== null) {
                $this->deleteGroup($child->groupId);
            }

            $this->deleteBranch($child->id);
        }
    }

    private function deleteGroup(GroupId $groupId): void
    {
        $this->writeGroupTranslateRepository->deleteByGroupId($groupId);
        $this->writeGroupRepository->delete($groupId);
    }
}