<?php

declare(strict_types=1);

namespace Tests\QueryBuilderTests;

use App\Database\QueryBuilder\MySqlQueryBuilder;

class MySqlQueryBuilderDataProviders
{
    public static function provideWhereSymbolConditions(): array
    {
        return [
            'WHERE clause with ? symbol' => [
                'SELECT id FROM words WHERE words.text = ?;',
                '?'
            ],
            'WHERE clause with : symbol' => [
                'SELECT id FROM words WHERE words.text = :word;',
                ':word'
            ],
            'WHERE clause with no symbol' => [
                "SELECT id FROM words WHERE words.text = 'word';",
                'word'
            ]
        ];
    }

    public static function provideWhereSymbolOperatorConditions(): array
    {
        return [
            'WHERE clause with IN operator' => [
                'SELECT id FROM words WHERE words.text IN (?, ?);',
                '?, ?',
                'IN'
            ],
            'WHERE clause with IS NULL operator' => [
                'SELECT id FROM words WHERE words.text IS NULL;',
                ' ',
                'IS NULL'
            ],
            'WHERE clause with IS NOT NULL operator' => [
                'SELECT id FROM words WHERE words.text IS NOT NULL;',
                ' ',
                'IS NOT NULL'
            ]
        ];
    }

    public static function provideQuery(): array
    {
        $queryBuilder = new MySqlQueryBuilder();

        return [
            'getSql() with WHERE clause' => [
                $queryBuilder
                    ->select('words', ['id'])
                    ->where('words.id', ' ', 'IS NULL')
                    ->getSql(),
                'SELECT id FROM words WHERE words.id IS NULL;'
            ],
            'getSql() with LEFT JOIN' => [
                $queryBuilder
                    ->select('syllables', ['syllable.text, syllables.id'])
                    ->leftJoin('hyphenationdb.hyphenated_words hw', 'syllables.id = hw.id')
                    ->where('hw.id', ' ', 'IS NULL')
                    ->getSql(),
                'SELECT syllable.text, syllables.id ' .
                'FROM syllables ' .
                'LEFT JOIN hyphenationdb.hyphenated_words hw ' .
                'ON syllables.id = hw.id ' .
                'WHERE hw.id IS NULL;'
            ],
            'getSql() with VALUES keyword' => [
                $queryBuilder
                    ->insert('words', ['text'])
                    ->values(['word1', 'word2'])
                    ->getSql(),
                'INSERT INTO words (text) VALUES word1, word2;'
            ]
        ];
    }


}
