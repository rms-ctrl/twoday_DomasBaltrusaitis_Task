<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enumerators\SqlStatement;

class Query
{
    private string $base = '';
    private ?SqlStatement $type = null;
    private array $where = [];
    private array $values = [];
    private string $leftJoin = '';

    public function __construct(
    ) {
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function getType(): string
    {
        return $this->type->value;
    }

    public function getWhere(): array
    {
        return $this->where;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getLeftJoin(): string
    {
        return $this->leftJoin;
    }

    public function setBase(string $base): void
    {
        $this->base = $base;
    }

    public function setType(SqlStatement $type): void
    {
        $this->type = $type;
    }

    public function setWhere(string $where): void
    {
        $this->where[] = $where;
    }

    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    public function setLeftJoin(string $leftJoin): void
    {
        $this->leftJoin = $leftJoin;
    }
}
