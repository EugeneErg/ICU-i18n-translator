<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Repositories;

interface TransactionManagerInterface
{
    public function transactional(callable $operation): mixed;
}