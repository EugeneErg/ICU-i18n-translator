<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

use EugeneErg\Translate\Entities\Group;
use EugeneErg\Translate\Entities\Path;
use EugeneErg\Translate\Entities\Translate;
use EugeneErg\Translate\ValueObjects\GroupId;
use EugeneErg\Translate\ValueObjects\PathId;

final readonly class EmptyTestRepository implements
    ReadGroupRepositoryInterface,
    ReadGroupTranslateRepositoryInterface,
    ReadPathRepositoryInterface,
    ReadTranslateRepositoryInterface,
    TransactionManagerInterface,
    WriteGroupRepositoryInterface,
    WriteGroupTranslateRepositoryInterface,
    WritePathRepositoryInterface,
    WriteTranslateRepositoryInterface
{

    public function findByPattern(string $originalPattern, ?string $context, ?string $locale = null): ?Group
    {
        return null;
    }

    public function find(GroupId $id): ?Group
    {
        return null;
    }

    public function list(GroupId $groupId, string $locale): array
    {
        // TODO: Implement list() method.
    }

    public function findRoot(string $value): ?Path
    {
        // TODO: Implement findRoot() method.
    }

    public function listByParentId(PathId $parentId): array
    {
        // TODO: Implement listByParentId() method.
    }

    public function findChild(string $value, PathId $parentId): ?Path
    {
        // TODO: Implement findChild() method.
    }

    public function findByGroup(GroupId $groupId, string $locale): ?Translate
    {
        // TODO: Implement findByGroup() method.
    }

    public function groupListByKey(GroupId $groupId, string $locale): array
    {
        // TODO: Implement groupListByKey() method.
    }

    public function keysListByKey(array $keys): array
    {
        // TODO: Implement keysListByKey() method.
    }

    public function transactional(callable $operation): mixed
    {
        // TODO: Implement transactional() method.
    }

    public function create(string $originalPattern, string $pattern, ?string $context, string $locale): Group
    {
        // TODO: Implement create() method.
    }

    public function delete(GroupId $id): void
    {
        // TODO: Implement delete() method.
    }

    public function deleteByGroupId(GroupId $groupId): void
    {
        // TODO: Implement deleteByGroupId() method.
    }
}