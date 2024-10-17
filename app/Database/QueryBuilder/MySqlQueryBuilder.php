<?php

declare(strict_types=1);

namespace App\Database\QueryBuilder;

use App\Entities\Query;
use App\Enumerators\SqlStatement;

class MySqlQueryBuilder implements SqlQueryBuilder
{
    protected Query $query;

    public function reset(): void
    {
        $this->query = new Query();
    }

    public function select(string $table, array $fields): SqlQueryBuilder
    {
        $this->reset();
        $this->query->setBase('SELECT ' . implode(', ', $fields) . ' FROM ' . $table);
        $this->query->setType(SqlStatement::SELECT);

        return $this;
    }

    public function insert(string $table, array $fields): SqlQueryBuilder
    {
        $this->reset();

        $fields = implode(', ', $fields);

        if (!empty($fields)) {
            $fields = ' (' . $fields . ')';
        }

        $this->query->setBase('INSERT INTO ' . $table .  $fields);
        $this->query->setType(SqlStatement::INSERT);

        return $this;
    }

    public function where(string $field, string $value, string $operator = '='): SqlQueryBuilder
    {
        if (!in_array($this->query->getType(), ['SELECT', 'UPDATE', 'DELETE'])) {
            throw new \Exception('Where clause can only be added to SELECT, UPDATE, DELETE');
        }

        switch ($value) {
            case str_starts_with($value, ':') || $value === '?':
                $this->query->setWhere("$field $operator $value");

                break;
            case $operator == 'IS NULL' || $operator == 'IS NOT NULL':
                $this->query->setWhere("$field $operator");

                break;
            case $operator == 'IN':
                $this->query->setWhere("$field $operator ($value)");

                break;
            default:
                $this->query->setWhere("$field $operator '$value'");
        }

        return $this;
    }

    public function values(array $values): SqlQueryBuilder
    {
        if ($this->query->getType() != 'INSERT') {
            throw new \LogicException(<<<EOT
            Values can currently only be added alongside the
            INSERT statement and do not function as a standalone statement.'
            EOT);
        }

        $this->query->setValues($values);

        return $this;
    }

    public function delete(string $table): SqlQueryBuilder
    {
        $this->reset();
        $this->query->setBase('DELETE FROM ' . $table);
        $this->query->setType(SqlStatement::DELETE);

        return $this;
    }

    public function update(string $table, array $fields): SqlQueryBuilder
    {
        $this->reset();

        foreach ($fields as $key => $field) {
            $fields[$key] =  $field . ' = ' . ':' . $field;
        }

        $this->query->setBase('UPDATE ' . $table . ' SET ' . implode(', ', $fields));
        $this->query->setType(SqlStatement::UPDATE);

        return $this;
    }

    public function leftJoin(string $table, string $join): SqlQueryBuilder
    {
        if ($this->query->getType() != 'SELECT') {
            throw new \Exception('Left-join clause can only be added to SELECT');
        }

        $this->query->setLeftJoin($table . ' ON ' . $join);

        return $this;
    }

    public function getSql(): string
    {
        $query = $this->query;
        $sql = $query->getBase();

        if (!empty($this->query->getLeftJoin())) {
            $sql .= ' LEFT JOIN ' . $this->query->getLeftJoin();
        }

        if (!empty($this->query->getWhere())) {
            $sql .= ' WHERE ' . implode(' AND ', $this->query->getWhere());
        }

        if (!empty($this->query->getValues())) {
            $sql .= ' VALUES ' . implode(', ', $this->query->getValues());
        }

        if (!empty($this->query->getBase())) {
            $sql .= ';';
        }

        return $sql;
    }
}
