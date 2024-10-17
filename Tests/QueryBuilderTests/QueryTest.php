<?php

declare(strict_types=1);

namespace Tests\QueryBuilderTests;

use App\Entities\Query;
use App\Enumerators\SqlStatement;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testBase(): void
    {
        $query = new Query();

        $query->setBase('SELECT FROM');
        $this->assertEquals('SELECT FROM', $query->getBase());
    }

    public function testType(): void
    {
        $query = new Query();

        $query->setType(SqlStatement::SELECT);
        $this->assertEquals(SqlStatement::SELECT->value, $query->getType());
    }

    public function testWhere(): void
    {
        $query = new Query();

        $query->setWhere('syllables');
        $this->assertContains('syllables', $query->getWhere());
    }

    public function testValues(): void
    {
        $query = new Query();

        $query->setValues(['syllable']);
        $this->assertContains('syllable', $query->getValues());
    }

    public function testLeftJoin(): void
    {
        $query = new Query();

        $query->setLeftJoin('words w on syllables.id = w.syllable_id');
        $this->assertEquals('words w on syllables.id = w.syllable_id', $query->getLeftJoin());
    }
}
