<?php

declare(strict_types=1);

namespace Tests;

use EugeneErg\IcuI18nTranslator\DataTransferObjects\FilePathContainer;
use EugeneErg\IcuI18nTranslator\DataTransferObjects\Variable;
use EugeneErg\IcuI18nTranslator\Entities\Group;
use EugeneErg\IcuI18nTranslator\Entities\GroupTranslate;
use EugeneErg\IcuI18nTranslator\Entities\Path;
use EugeneErg\IcuI18nTranslator\Entities\Translate;
use EugeneErg\IcuI18nTranslator\Exceptions\FileNotFoundException;
use EugeneErg\IcuI18nTranslator\Exceptions\FormatNotFoundException;
use EugeneErg\IcuI18nTranslator\Exceptions\GroupNotFoundException;
use EugeneErg\IcuI18nTranslator\Exceptions\IncorrectTransferPatternException;
use EugeneErg\IcuI18nTranslator\Exceptions\UnexpectedTranslateDirectionException;
use EugeneErg\IcuI18nTranslator\FormatterInterface;
use EugeneErg\IcuI18nTranslator\Repositories\ReadGroupRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Repositories\ReadPathRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Repositories\ReadTranslateRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Repositories\WriteGroupRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Repositories\WriteGroupTranslateRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Repositories\WritePathRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Repositories\WriteTranslateRepositoryInterface;
use EugeneErg\IcuI18nTranslator\Translator;
use EugeneErg\IcuI18nTranslator\TranslatorInterface;
use EugeneErg\IcuI18nTranslator\ValueObjects\GroupId;
use EugeneErg\IcuI18nTranslator\ValueObjects\PathId;
use EugeneErg\IcuI18nTranslator\ValueObjects\Translated;
use EugeneErg\IcuI18nTranslator\ValueObjects\TranslateId;
use EugeneErg\ICUMessageFormatParser\Parser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

use function is_string;

/**
 * @phpstan-type RepoStubs array{
 *   readGroup: ReadGroupRepositoryInterface&Stub,
 *   writeGroup: WriteGroupRepositoryInterface&Stub,
 *   readTranslate: ReadTranslateRepositoryInterface&Stub,
 *   writeTranslate: WriteTranslateRepositoryInterface&Stub,
 *   writeGroupTranslate: WriteGroupTranslateRepositoryInterface&Stub,
 *   readPath: ReadPathRepositoryInterface&Stub,
 *   writePath: WritePathRepositoryInterface&Stub,
 * }
 *
 * @internal
 */
final class TranslatorTest extends TestCase
{
    /**
     * @phpstan-ignore property.uninitialized
     */
    private Parser $parser;

    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    // -------------------------------------------------------------------------
    // translateText
    // -------------------------------------------------------------------------

    /**
     * When the group exists and a translation is already cached — return it directly.
     */
    #[Test]
    public function translateTextReturnsCachedTranslation(): void
    {
        $s = $this->stubs();
        $group = $this->makeGroup(originalPattern: 'Hello world', pattern: '0', locale: 'en');
        $translate = $this->makeTranslate(pattern: 'Bonjour monde', locale: 'fr');

        $s['readGroup']->method('findByPattern')->willReturn($group);
        $s['readTranslate']->method('findByGroup')->willReturn($translate);

        $result = $this->makeTranslator($s)->translateText('Hello world', 'fr', 'en');

        $this->assertSame('Bonjour monde', $result);
    }

    /**
     * When neither group nor cached translate exist — the external translator is called.
     */
    #[Test]
    public function translateTextCallsExternalTranslatorWhenNoCacheExists(): void
    {
        $s = $this->stubs();
        $s['readGroup']->method('findByPattern')->willReturn(null);
        $s['readTranslate']->method('find')->willReturn(null);

        $group = $this->makeGroup(originalPattern: 'Hello world', pattern: '0', locale: 'en');
        $sourceTranslate = $this->makeTranslate(id: 'src', pattern: 'Hello world', locale: 'en');
        $targetTranslate = $this->makeTranslate(id: 'tgt', pattern: 'Bonjour monde', locale: 'fr');

        $s['writeGroup']->method('create')->willReturn($group);
        $s['writeTranslate']->method('create')
            ->willReturnOnConsecutiveCalls($sourceTranslate, $targetTranslate);
        $s['writeGroupTranslate']->method('create')->willReturn($this->makeGroupTranslate());

        $ext = $this->createStub(TranslatorInterface::class);
        $ext->method('canTranslate')->willReturn(true);
        $ext->method('translate')->willReturnCallback(
            static fn (array $p): array => array_map(
                static fn (mixed $x): mixed => is_string($x) ? 'Bonjour monde' : $x,
                $p,
            ),
        );

        $result = $this->makeTranslator($s, $ext)->translateText('Hello world', 'fr', 'en');

        $this->assertSame('Bonjour monde', $result);
    }

    /**
     * When fromLocale is null and the pattern is not in DB — language detection is used.
     */
    #[Test]
    public function translateTextUsesLanguageDetectionWhenFromLocaleUnknown(): void
    {
        $s = $this->stubs();
        $s['readGroup']->method('findByPattern')->willReturn(null);
        $s['readTranslate']->method('find')->willReturn(null);

        $group = $this->makeGroup(locale: 'en');
        $sourceTranslate = $this->makeTranslate(id: 'src', pattern: 'Hello world', locale: 'en');
        $targetTranslate = $this->makeTranslate(id: 'tgt', pattern: 'Hallo Welt', locale: 'de');

        $s['writeGroup']->method('create')->willReturn($group);
        $s['writeTranslate']->method('create')
            ->willReturnOnConsecutiveCalls($sourceTranslate, $targetTranslate);
        $s['writeGroupTranslate']->method('create')->willReturn($this->makeGroupTranslate());

        $ext = $this->createStub(TranslatorInterface::class);
        $ext->method('canTranslate')->willReturn(true);
        $ext->method('translateWithDetect')->willReturnCallback(
            static function (array $p): Translated {
                /** @var array<string|Variable> $pattern */
                $pattern = array_map(static fn (mixed $x): mixed => is_string($x) ? 'Hallo Welt' : $x, $p);

                return new Translated('en', $pattern);
            },
        );

        $result = $this->makeTranslator($s, $ext)->translateText('Hello world', 'de');

        $this->assertSame('Hallo Welt', $result);
    }

    /**
     * When fromLocale is null and the pattern IS in DB — locale is inferred from the stored record.
     */
    #[Test]
    public function translateTextInfersFromLocaleFromDatabase(): void
    {
        $s = $this->stubs();
        $storedSource = $this->makeTranslate(id: 'src', pattern: 'Hello world', locale: 'en');
        $targetTranslate = $this->makeTranslate(id: 'tgt', pattern: 'Bonjour monde', locale: 'fr');
        $group = $this->makeGroup(locale: 'en');

        $s['readGroup']->method('findByPattern')->willReturn(null);
        // find('Hello world', null) → storedSource  (infer locale = 'en')
        // find('Bonjour monde', 'fr') → null         (not cached, triggers create)
        $s['readTranslate']->method('find')->willReturnMap([
            ['Hello world', null, $storedSource],
            ['Bonjour monde', 'fr', null],
        ]);

        $s['writeGroup']->method('create')->willReturn($group);
        $s['writeTranslate']->method('create')->willReturn($targetTranslate);
        $s['writeGroupTranslate']->method('create')->willReturn($this->makeGroupTranslate());

        $ext = $this->createStub(TranslatorInterface::class);
        $ext->method('canTranslate')->willReturn(true);
        $ext->method('translate')->willReturnCallback(
            static fn (array $p): array => array_map(
                static fn (mixed $x): mixed => is_string($x) ? '' : $x,
                $p,
            ),
        );

        $result = $this->makeTranslator($s, $ext)->translateText('Hello world', 'fr');

        $this->assertSame('Bonjour monde', $result);
    }

    /**
     * When no external translator can handle the direction — UnexpectedTranslateDirectionException.
     * fromLocale is given + group=null → else branch → group & source created → translate() throws.
     */
    #[Test]
    public function translateTextThrowsWhenNoTranslatorAvailable(): void
    {
        $s = $this->stubs();
        $s['readGroup']->method('findByPattern')->willReturn(null);
        $s['writeGroup']->method('create')->willReturn($this->makeGroup(locale: 'en'));
        $s['writeTranslate']->method('create')->willReturn($this->makeTranslate());
        $s['writeGroupTranslate']->method('create')->willReturn($this->makeGroupTranslate());

        $ext = $this->createStub(TranslatorInterface::class);
        $ext->method('canTranslate')->willReturn(false);

        $this->expectException(UnexpectedTranslateDirectionException::class);

        $this->makeTranslator($s, $ext)->translateText('Hello world', 'fr', 'en');
    }

    // -------------------------------------------------------------------------
    // translateMessage — ICU plural
    // -------------------------------------------------------------------------

    /**
     * ICU plural: cached translation for 'one' case (key=0) is returned when count=1.
     */
    #[Test]
    public function translateMessageReturnsCachedPluralTranslation(): void
    {
        $s = $this->stubs();
        $pattern = '{count, plural, one {# item} other {# items}}';
        $group = $this->makeGroup(originalPattern: $pattern, pattern: '{count, plural, one {0} other {1}}', locale: 'en');
        $translateOne = $this->makeTranslate(pattern: '{count} élément', locale: 'fr');

        $s['readGroup']->method('findByPattern')->willReturn($group);
        $s['readTranslate']->method('findByGroup')->willReturnCallback(
            static fn (GroupId $gId, string $key, string $locale): Translate|null => ($locale === 'fr' && $key === '0') ? $translateOne : null,
        );

        $result = $this->makeTranslator($s)->translateMessage($pattern, ['count' => 1], 'fr', 'en');

        $this->assertSame('1 élément', $result);
    }

    // -------------------------------------------------------------------------
    // getGroups
    // -------------------------------------------------------------------------

    #[Test]
    public function getGroupsReturnsPaginatedList(): void
    {
        $s = $this->stubs();
        $groups = [$this->makeGroup('g1'), $this->makeGroup('g2')];

        /** @var MockObject&ReadGroupRepositoryInterface $mock */
        $mock = $this->createMock(ReadGroupRepositoryInterface::class);
        $mock->expects($this->once())
            ->method('list')
            ->with(10, 5)        // offset=(3-1)*5=10, limit=5
            ->willReturn($groups);

        $s['readGroup'] = $mock;
        $result = $this->makeTranslator($s)->getGroups(pageSize: 5, page: 3);

        $this->assertSame($groups, $result);
    }

    #[Test]
    public function getGroupsFirstPageUsesZeroOffset(): void
    {
        $s = $this->stubs();

        /** @var MockObject&ReadGroupRepositoryInterface $mock */
        $mock = $this->createMock(ReadGroupRepositoryInterface::class);
        $mock->expects($this->once())
            ->method('list')
            ->with(0, 10)
            ->willReturn([]);

        $s['readGroup'] = $mock;
        $this->makeTranslator($s)->getGroups(pageSize: 10);
    }

    // -------------------------------------------------------------------------
    // getTranslates
    // -------------------------------------------------------------------------

    #[Test]
    public function getTranslatesThrowsWhenGroupNotFound(): void
    {
        $s = $this->stubs();
        $s['readGroup']->method('find')->willReturn(null);

        $this->expectException(GroupNotFoundException::class);

        $this->makeTranslator($s)->getTranslates(new GroupId('unknown'), 'en');
    }

    #[Test]
    public function getTranslatesReturnsTranslatesWithCases(): void
    {
        $s = $this->stubs();
        $pattern = '{count, plural, one {# item} other {# items}}';
        $group = $this->makeGroup(originalPattern: $pattern);
        $translateOne = $this->makeTranslate(pattern: '{count} article', locale: 'fr');

        $s['readGroup']->method('find')->willReturn($group);
        $s['readTranslate']->method('groupListByKey')->willReturn(['0' => $translateOne]);

        $result = $this->makeTranslator($s)->getTranslates(new GroupId('g1'), 'fr');

        $this->assertArrayHasKey('0', $result);
        $this->assertArrayHasKey('1', $result);

        /** @var \EugeneErg\IcuI18nTranslator\DataTransferObjects\Translate[] $result */
        $this->assertSame('{count} article', $result[0]->pattern);
        $this->assertNull($result[1]->pattern);
    }

    // -------------------------------------------------------------------------
    // setTranslate
    // -------------------------------------------------------------------------

    #[Test]
    public function setTranslateThrowsOnMultipleVariants(): void
    {
        $s = $this->stubs();

        $this->expectException(IncorrectTransferPatternException::class);

        $this->makeTranslator($s)->setTranslate(
            new GroupId('g1'),
            '0',
            'fr',
            '{count, plural, one {# item} other {# items}}',
        );
    }

    #[Test]
    public function setTranslateCreatesNewTranslateWhenNotFound(): void
    {
        $s = $this->stubs();
        $newTranslate = $this->makeTranslate(id: 'new', pattern: 'Bonjour', locale: 'fr');

        $s['readTranslate']->method('find')->willReturn(null);

        /** @var MockObject&WriteTranslateRepositoryInterface $mockWT */
        $mockWT = $this->createMock(WriteTranslateRepositoryInterface::class);
        $mockWT->expects($this->once())->method('create')->with('Bonjour', 'fr')->willReturn($newTranslate);
        $s['writeTranslate'] = $mockWT;

        /** @var MockObject&WriteGroupTranslateRepositoryInterface $mockWGT */
        $mockWGT = $this->createMock(WriteGroupTranslateRepositoryInterface::class);
        $mockWGT->expects($this->once())->method('deleteByGroupId');
        $mockWGT->expects($this->once())->method('create')->willReturn($this->makeGroupTranslate());
        $s['writeGroupTranslate'] = $mockWGT;

        $result = $this->makeTranslator($s)->setTranslate(new GroupId('g1'), '0', 'fr', 'Bonjour');

        $this->assertSame('new', $result->id->value);
    }

    #[Test]
    public function setTranslateReusesExistingTranslate(): void
    {
        $s = $this->stubs();
        $existingTranslate = $this->makeTranslate(id: 'existing', pattern: 'Bonjour', locale: 'fr');

        $s['readTranslate']->method('find')->willReturn($existingTranslate);

        /** @var MockObject&WriteTranslateRepositoryInterface $mockWT */
        $mockWT = $this->createMock(WriteTranslateRepositoryInterface::class);
        $mockWT->expects($this->never())->method('create');
        $s['writeTranslate'] = $mockWT;

        $s['writeGroupTranslate']->method('create')->willReturn($this->makeGroupTranslate());

        $result = $this->makeTranslator($s)->setTranslate(new GroupId('g1'), '0', 'fr', 'Bonjour');

        $this->assertSame('existing', $result->id->value);
    }

    // -------------------------------------------------------------------------
    // deleteTranslateFromGroup / deleteTranslate
    // -------------------------------------------------------------------------

    #[Test]
    public function deleteTranslateFromGroupDelegatesCorrectly(): void
    {
        $s = $this->stubs();
        $groupId = new GroupId('g1');

        /** @var MockObject&WriteGroupTranslateRepositoryInterface $mock */
        $mock = $this->createMock(WriteGroupTranslateRepositoryInterface::class);
        $mock->expects($this->once())->method('deleteByGroupId')->with($groupId, '0', 'fr');
        $s['writeGroupTranslate'] = $mock;

        $this->makeTranslator($s)->deleteTranslateFromGroup($groupId, '0', 'fr');
    }

    #[Test]
    public function deleteTranslateDelegatesCorrectly(): void
    {
        $s = $this->stubs();
        $translateId = new TranslateId('t1');

        /** @var MockObject&WriteTranslateRepositoryInterface $mock */
        $mock = $this->createMock(WriteTranslateRepositoryInterface::class);
        $mock->expects($this->once())->method('delete')->with($translateId);
        $s['writeTranslate'] = $mock;

        $this->makeTranslator($s)->deleteTranslate($translateId);
    }

    // -------------------------------------------------------------------------
    // File management
    // -------------------------------------------------------------------------

    #[Test]
    public function findFileDelegatesToRepository(): void
    {
        $s = $this->stubs();
        $path = $this->makePath();

        /** @var MockObject&ReadPathRepositoryInterface $mock */
        $mock = $this->createMock(ReadPathRepositoryInterface::class);
        $mock->expects($this->once())->method('findRoot')->with('messages')->willReturn($path);
        $s['readPath'] = $mock;

        $this->assertSame($path, $this->makeTranslator($s)->findFile('messages'));
    }

    #[Test]
    public function findFileReturnsNullWhenNotFound(): void
    {
        $s = $this->stubs();
        $s['readPath']->method('findRoot')->willReturn(null);

        $this->assertNull($this->makeTranslator($s)->findFile('missing'));
    }

    #[Test]
    public function getFilesReturnsPaginatedList(): void
    {
        $s = $this->stubs();
        $paths = [$this->makePath('p1'), $this->makePath('p2')];

        /** @var MockObject&ReadPathRepositoryInterface $mock */
        $mock = $this->createMock(ReadPathRepositoryInterface::class);
        $mock->expects($this->once())->method('listRoot')
            ->with(20, 10)   // offset=(3-1)*10=20, limit=10
            ->willReturn($paths);
        $s['readPath'] = $mock;

        $result = $this->makeTranslator($s)->getFiles(pageSize: 10, page: 3);

        $this->assertSame($paths, $result);
    }

    #[Test]
    public function getFileBranchDelegatesCorrectly(): void
    {
        $s = $this->stubs();
        $parentId = new PathId('p1');
        $children = [$this->makePath('p2', 'child', 'p1')];

        /** @var MockObject&ReadPathRepositoryInterface $mock */
        $mock = $this->createMock(ReadPathRepositoryInterface::class);
        $mock->expects($this->once())->method('listByParentId')->with($parentId)->willReturn($children);
        $s['readPath'] = $mock;

        $this->assertSame($children, $this->makeTranslator($s)->getFileBranch($parentId));
    }

    #[Test]
    public function createEmptyFileCreatesRootPath(): void
    {
        $s = $this->stubs();
        $path = $this->makePath('p1', 'messages');

        /** @var MockObject&WritePathRepositoryInterface $mock */
        $mock = $this->createMock(WritePathRepositoryInterface::class);
        $mock->expects($this->once())->method('create')->with('messages', null, null)->willReturn($path);
        $s['writePath'] = $mock;

        $result = $this->makeTranslator($s)->createEmptyFile('messages');

        $this->assertSame('p1', $result->value);
    }

    #[Test]
    public function addFilePathCreatesChildPath(): void
    {
        $s = $this->stubs();
        $parentId = new PathId('p1');
        $child = $this->makePath('p2', 'section', 'p1');

        /** @var MockObject&WritePathRepositoryInterface $mock */
        $mock = $this->createMock(WritePathRepositoryInterface::class);
        $mock->expects($this->once())->method('create')->with('section', $parentId, null)->willReturn($child);
        $s['writePath'] = $mock;

        $result = $this->makeTranslator($s)->addFilePath($parentId, 'section');

        $this->assertSame('p2', $result->value);
    }

    #[Test]
    public function deleteFileBranchDoesNothingWhenPathNotFound(): void
    {
        $s = $this->stubs();
        $s['readPath']->method('findById')->willReturn(null);

        /** @var MockObject&WritePathRepositoryInterface $mock */
        $mock = $this->createMock(WritePathRepositoryInterface::class);
        $mock->expects($this->never())->method('delete');
        $s['writePath'] = $mock;

        $this->makeTranslator($s)->deleteFileBranch(new PathId('unknown'));
    }

    #[Test]
    public function deleteFileBranchDeletesLeafPath(): void
    {
        $s = $this->stubs();
        $path = $this->makePath('p1', 'messages');

        /** @var MockObject&ReadPathRepositoryInterface $readMock */
        $readMock = $this->createMock(ReadPathRepositoryInterface::class);
        $readMock->expects($this->once())->method('findById')->with($path->id)->willReturn($path);
        $readMock->method('listByParentId')->willReturn([]);
        $s['readPath'] = $readMock;

        /** @var MockObject&WritePathRepositoryInterface $writeMock */
        $writeMock = $this->createMock(WritePathRepositoryInterface::class);
        $writeMock->expects($this->once())->method('delete')->with($path->id);
        $s['writePath'] = $writeMock;

        $this->makeTranslator($s)->deleteFileBranch($path->id);
    }

    #[Test]
    public function deleteFileBranchRecursivelyDeletesChildren(): void
    {
        $s = $this->stubs();
        $root = $this->makePath('p1', 'root');
        $child = $this->makePath('p2', 'child', 'p1');

        $s['readPath']->method('findById')->willReturn($root);
        $s['readPath']->method('listByParentId')->willReturnCallback(
            static fn (PathId $id): array => $id->value === 'p1' ? [$child] : [],
        );

        /** @var MockObject&WritePathRepositoryInterface $mock */
        $mock = $this->createMock(WritePathRepositoryInterface::class);
        $mock->expects($this->exactly(2))->method('delete');
        $s['writePath'] = $mock;

        $this->makeTranslator($s)->deleteFileBranch($root->id);
    }

    // -------------------------------------------------------------------------
    // addFile / getFile
    // -------------------------------------------------------------------------

    #[Test]
    public function addFileThrowsWhenFormatNotFound(): void
    {
        $this->expectException(FormatNotFoundException::class);
        $this->makeTranslator($this->stubs())->addFile('xml', 'messages', '<root/>', 'en');
    }

    #[Test]
    public function getFileThrowsWhenFormatNotFound(): void
    {
        $this->expectException(FormatNotFoundException::class);
        $this->makeTranslator($this->stubs())->getFile('xml', 'messages', 'en');
    }

    #[Test]
    public function getFileThrowsWhenFileNotFound(): void
    {
        $s = $this->stubs();
        $s['readPath']->method('findRoot')->willReturn(null);

        $formatter = $this->createStub(FormatterInterface::class);

        $this->expectException(FileNotFoundException::class);

        $this->makeTranslator($s, formatter: $formatter)->getFile('json', 'messages', 'en');
    }

    #[Test]
    public function getFileReturnsFormattedContent(): void
    {
        $s = $this->stubs();
        $root = $this->makePath('p1', 'messages');

        $s['readPath']->method('findRoot')->willReturn($root);
        $s['readPath']->method('listByParentId')->willReturn([]);

        $formatter = $this->createStub(FormatterInterface::class);
        $formatter->method('format')->willReturn('{"key":"value"}');

        $result = $this->makeTranslator($s, formatter: $formatter)->getFile('json', 'messages', 'en');

        $this->assertSame('{"key":"value"}', $result);
    }

    // -------------------------------------------------------------------------
    // IncorrectTransferPatternException via setTranslate
    // -------------------------------------------------------------------------

    /**
     * setTranslate rejects any pattern that produces more than 1 ICU case.
     */
    #[Test]
    public function setTranslateThrowsIncorrectPatternExceptionOnPluralInput(): void
    {
        $this->expectException(IncorrectTransferPatternException::class);

        $this->makeTranslator($this->stubs())->setTranslate(
            new GroupId('g1'),
            '0',
            'fr',
            '{count, plural, one {# item} other {# items}}',
        );
    }

    // =========================================================================
    // Regression tests for fixed bugs
    // =========================================================================

    // -------------------------------------------------------------------------
    // Bug 1: sourceTranslate stored with $toLocale instead of detected $fromLocale
    // -------------------------------------------------------------------------

    /**
     * When translateWithDetect detects locale='en' and target is 'de',
     * the sourceTranslate record must be written with locale='en', not 'de'.
     *
     * Buggy:  ->create(pattern: $variantPattern, locale: $toLocale)
     * Fixed:  ->create(pattern: $variantPattern, locale: $fromLocale)
     */
    #[Test]
    public function translateTextWithDetectStoresSourceTranslateWithDetectedLocale(): void
    {
        $s = $this->stubs();
        $s['readGroup']->method('findByPattern')->willReturn(null);
        $s['readTranslate']->method('find')->willReturn(null);

        $detectedLocale = 'en';
        $toLocale = 'de';
        $sourcePattern = 'Hello world';
        $translatedPat = 'Hallo Welt';

        $group = $this->makeGroup(locale: $detectedLocale);
        $targetTranslate = $this->makeTranslate(id: 'tgt', pattern: $translatedPat, locale: $toLocale);

        $s['writeGroup']->method('create')->willReturn($group);
        $s['writeGroupTranslate']->method('create')->willReturn($this->makeGroupTranslate());

        /** @var MockObject&WriteTranslateRepositoryInterface $mockWT */
        $mockWT = $this->createMock(WriteTranslateRepositoryInterface::class);
        $mockWT->expects($this->exactly(2))->method('create')
            ->willReturnCallback(function (
                string $pattern,
                string $locale,
            ) use ($detectedLocale, $toLocale, $sourcePattern, $targetTranslate): Translate {
                if ($pattern === $sourcePattern) {
                    // Bug: locale would be $toLocale='de'; fix: must be $detectedLocale='en'
                    self::assertSame(
                        $detectedLocale,
                        $locale,
                        'sourceTranslate must be stored with the detected fromLocale, not toLocale',
                    );

                    return $this->makeTranslate(id: 'src', pattern: $pattern, locale: $locale);
                }

                self::assertSame($toLocale, $locale);

                return $targetTranslate;
            });
        $s['writeTranslate'] = $mockWT;

        $ext = $this->createStub(TranslatorInterface::class);
        $ext->method('canTranslate')->willReturn(true);
        $ext->method('translateWithDetect')->willReturnCallback(
            static fn (array $p): Translated => new Translated(
                $detectedLocale,
                array_map(
                    static fn (string|Variable $x): string|Variable => is_string($x) ? $translatedPat : $x,
                    $p,
                ),
            ),
        );

        $result = $this->makeTranslator($s, $ext)->translateText($sourcePattern, $toLocale);

        $this->assertSame($translatedPat, $result);
    }

    // -------------------------------------------------------------------------
    // Bug 2: deleteBranch (called from saveGroup) does not delete child path records
    // -------------------------------------------------------------------------

    /**
     * When addFile replaces a directory-path node (groupId=null) with a leaf,
     * deleteBranch is called to clean up its subtree.
     * Every child path in that subtree must have writePathRepository->delete() called.
     *
     * Buggy:  deleteBranch() recurses and calls deleteGroup() but never deletes path rows
     * Fixed:  $this->writePathRepository->delete($child->id) added inside the loop
     */
    #[Test]
    public function saveGroupViaDeletBranchDeletesChildPathRecords(): void
    {
        $s = $this->stubs();

        $rootPath = $this->makePath('p1', 'messages');
        $dirPath = $this->makePath('p2', 'greeting', 'p1');        // groupId=null → deleteBranch triggered
        $childOfDir = $this->makePath('p3', 'sub', 'p2', 'g_old');   // must be deleted by deleteBranch
        $newGroup = $this->makeGroup('g_new', 'Bonjour', '0', 'fr');
        $newPath = $this->makePath('p4', 'greeting', 'p1', 'g_new');

        $s['readPath']->method('findRoot')->willReturn($rootPath);
        $s['readPath']->method('findChild')->willReturn($dirPath);
        $s['readPath']->method('listByParentId')->willReturnCallback(
            static fn (PathId $id): array => $id->value === 'p2' ? [$childOfDir] : [],
        );
        $s['readGroup']->method('findByPattern')->willReturn(null);
        $s['writeGroup']->method('create')->willReturn($newGroup);

        $deletedIds = [];
        $writePath = $this->createStub(WritePathRepositoryInterface::class);
        $writePath->method('delete')->willReturnCallback(
            static function (PathId $id) use (&$deletedIds): void {
                $deletedIds[] = $id->value;
            },
        );
        $writePath->method('create')->willReturn($newPath);
        $s['writePath'] = $writePath;

        $formatter = $this->createStub(FormatterInterface::class);
        $formatter->method('parse')->willReturn(
            new FilePathContainer(
                children: ['greeting' => 'Bonjour'],
            ),
        );

        $this->makeTranslator($s, formatter: $formatter)
            ->addFile('json', 'messages', '{"greeting":"Bonjour"}', 'fr');

        // child path p3 must have been deleted — bug: it was never deleted
        $this->assertContains('p3', $deletedIds, 'deleteBranch must delete child path records');
    }

    // -------------------------------------------------------------------------
    // Bug 3: saveGroup — $path not reset after deleteBranch, new group never created
    // -------------------------------------------------------------------------

    /**
     * When addFile writes to a key that currently maps to a directory path (groupId=null),
     * deleteBranch cleans up the subtree. After the fix $path is set to null, so the
     * `if ($path === null)` block runs and creates the new group+path.
     *
     * Buggy:  $path stays non-null after deleteBranch → `if ($path === null)` is skipped →
     *         no new group or path ever created (silent no-op)
     * Fixed:  $path = null after deleteBranch
     */
    #[Test]
    public function addFileCreatesGroupWhenDirectoryPathIsReplaced(): void
    {
        $s = $this->stubs();

        $rootPath = $this->makePath('p1', 'messages');
        // Existing path is a directory (groupId = null) → condition fires, deleteBranch runs
        $dirPath = $this->makePath('p2', 'greeting', 'p1'); // no groupId
        $newGroup = $this->makeGroup('g_new', 'Bonjour', '0', 'fr');
        $newPath = $this->makePath('p3', 'greeting', 'p1', 'g_new');

        $s['readPath']->method('findRoot')->willReturn($rootPath);
        $s['readPath']->method('findChild')->willReturn($dirPath);
        $s['readPath']->method('listByParentId')->willReturn([]);
        $s['readGroup']->method('findByPattern')->willReturn(null);

        /** @var MockObject&WriteGroupRepositoryInterface $mockWG */
        $mockWG = $this->createMock(WriteGroupRepositoryInterface::class);
        // A new group MUST be created — bug: never reached because $path stays non-null
        $mockWG->expects($this->once())->method('create')->willReturn($newGroup);
        $s['writeGroup'] = $mockWG;

        /** @var MockObject&WritePathRepositoryInterface $mockWP */
        $mockWP = $this->createMock(WritePathRepositoryInterface::class);
        // A new path MUST be created — bug: never reached
        $mockWP->expects($this->once())->method('create')->willReturn($newPath);
        $s['writePath'] = $mockWP;

        $formatter = $this->createStub(FormatterInterface::class);
        $formatter->method('parse')->willReturn(
            new FilePathContainer(
                children: ['greeting' => 'Bonjour'],
            ),
        );

        $this->makeTranslator($s, formatter: $formatter)
            ->addFile('json', 'messages', '{"greeting":"Bonjour"}', 'fr');
    }

    // -------------------------------------------------------------------------
    // Factories
    // -------------------------------------------------------------------------

    /**
     * @return RepoStubs
     */
    private function stubs(): array
    {
        return [
            'readGroup' => $this->createStub(ReadGroupRepositoryInterface::class),
            'writeGroup' => $this->createStub(WriteGroupRepositoryInterface::class),
            'readTranslate' => $this->createStub(ReadTranslateRepositoryInterface::class),
            'writeTranslate' => $this->createStub(WriteTranslateRepositoryInterface::class),
            'writeGroupTranslate' => $this->createStub(WriteGroupTranslateRepositoryInterface::class),
            'readPath' => $this->createStub(ReadPathRepositoryInterface::class),
            'writePath' => $this->createStub(WritePathRepositoryInterface::class),
        ];
    }

    /**
     * @param RepoStubs $s
     */
    private function makeTranslator(
        array $s,
        TranslatorInterface|null $externalTranslator = null,
        FormatterInterface|null $formatter = null,
    ): Translator {
        return new Translator(
            readGroupRepository: $s['readGroup'],
            writeGroupRepository: $s['writeGroup'],
            readTranslateRepository: $s['readTranslate'],
            writeTranslateRepository: $s['writeTranslate'],
            writeGroupTranslateRepository: $s['writeGroupTranslate'],
            readPathRepository: $s['readPath'],
            writePathRepository: $s['writePath'],
            parser: $this->parser,
            translators: $externalTranslator !== null ? [$externalTranslator] : [],
            formatters: $formatter !== null ? ['json' => $formatter] : [],
        );
    }

    private function makeGroup(
        string $id = 'g1',
        string $originalPattern = 'Hello world',
        string $pattern = '0',
        string $locale = 'en',
        string|null $context = null,
    ): Group {
        return new Group(new GroupId($id), $originalPattern, $pattern, $locale, $context);
    }

    private function makeTranslate(
        string $id = 't1',
        string $pattern = 'Hello world',
        string $locale = 'en',
    ): Translate {
        return new Translate(new TranslateId($id), $pattern, $locale);
    }

    private function makeGroupTranslate(
        string $groupId = 'g1',
        string $translateId = 't1',
        string $key = '0',
        string|null $sourceId = null,
    ): GroupTranslate {
        return new GroupTranslate(
            new GroupId($groupId),
            new TranslateId($translateId),
            $key,
            $sourceId !== null ? new TranslateId($sourceId) : null,
        );
    }

    private function makePath(
        string $id = 'p1',
        string $value = 'messages',
        string|null $parentId = null,
        string|null $groupId = null,
    ): Path {
        return new Path(
            new PathId($id),
            $value,
            $parentId !== null ? new PathId($parentId) : null,
            $groupId !== null ? new GroupId($groupId) : null,
        );
    }
}
