<?php

declare(strict_types=1);

namespace Tests\QueryBuilderTests;

use App\Database\QueryBuilder\MySqlQueryBuilder;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class MySqlQueryBuilderTest extends TestCase
{
    public function testReset(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $queryBuilder->select('words', ['id']);
        $queryBuilder->reset();

        $query = $queryBuilder->getSql();
        $this->assertEmpty($query);
    }

    public function testSelect(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->select('words', ['id'])
            ->getSql();

        $this->assertEquals('SELECT id FROM words;', $query);
    }

    public function testInsert(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->insert('words', ['id', 'text'])
            ->getSql();

        $this->assertEquals('INSERT INTO words (id, text);', $query);
    }

    public function testWhereException(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $this->expectException(\Exception::class);

        $queryBuilder
            ->insert('words', ['id', 'text'])
            ->where('words', '?')
            ->getSql();
    }

    #[DataProviderExternal(MySqlQueryBuilderDataProviders::class, 'provideWhereSymbolConditions')]
    public function testWhereConditionsWithSymbols(string $expectedResult, string $condition): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->select('words', ['id'])
            ->where('words.text', $condition)
            ->getSql();

        $this->assertEquals($expectedResult, $query);
    }

    #[DataProviderExternal(MySqlQueryBuilderDataProviders::class, 'provideWhereSymbolOperatorConditions')]
    public function testWhereConditionsWithOperators(string $expectedResult, string $condition, string $operator): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->select('words', ['id'])
            ->where('words.text', $condition, $operator)
            ->getSql();

        $this->assertEquals($expectedResult, $query);
    }

    public function testValuesIfClauseNotInsert(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $this->expectException(\Exception::class);

        $queryBuilder
            ->select('words', ['id'])
            ->values([1, 2, 3])
            ->getSql();
    }

    public function testValues(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->insert('words', ['text'])
            ->values(['word1', 'word2'])
            ->getSql();

        $this->assertEquals('INSERT INTO words (text) VALUES word1, word2;', $query);
    }

    public function testDelete(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->delete('words')
            ->getSql();

        $this->assertEquals('DELETE FROM words;', $query);
    }

    public function testUpdate(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->update('words', ['text'])
            ->where('words.id', ' ', 'IS NULL')
            ->getSql();

        $this->assertEquals('UPDATE words SET text = :text WHERE words.id IS NULL;', $query);
    }

    public function testLeftJoin(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->select('syllables', ['syllable.text, syllables.id'])
            ->leftJoin('hyphenationdb.hyphenated_words hw', 'syllables.id = hw.id')
            ->where('hw.id', '', 'IS NULL')
            ->getSql();

        $this->assertEquals('SELECT syllable.text, syllables.id '.
            'FROM syllables ' .
            'LEFT JOIN hyphenationdb.hyphenated_words hw ' .
            'ON syllables.id = hw.id ' .
            'WHERE hw.id IS NULL ;',
            $query
        );
    }

    public function testLeftJoinIfClauseIsInsert(): void
    {
        $queryBuilder = new MySqlQueryBuilder();

        $this->expectException(\Exception::class);

        $queryBuilder
            ->insert('words', ['text'])
            ->leftJoin('words w', 'syllables.id = w.id');
    }

    #[DataProviderExternal(MySqlQueryBuilderDataProviders::class, 'provideQuery')]
    public function testGetSql(string $query, string $expectedResult): void
    {
        $this->assertEquals($expectedResult, $query);
    }
}
