<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\QueryBuilder\SqlQueryBuilder;
use App\Entities\Word;

readonly class WordRepository
{
    public function __construct(
        private SqlQueryBuilder $sqlQueryBuilder,
        private \PDO $connection,
    ) {
    }

    public function getAllWords(bool $asString = false): array
    {
        $words = [];
        $stringWords = [];

        $queryString = $this->sqlQueryBuilder
            ->select('words', ['words.text, words.id'])
            ->leftJoin('hyphenationdb.hyphenated_words hw', 'words.id = hw.word_id')
            ->where('hw.word_id', '', 'IS NULL')
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute();

        $wordRows = $query->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($wordRows as $wordRow) {
            $words[] = new Word($wordRow['id'], $wordRow['text']);
            $stringWords[$wordRow['id']] = $wordRow['text'];
        }

        return $asString ? $stringWords : $words;
    }

    public function deleteWord($wordId): int
    {
        $queryString = $this->sqlQueryBuilder
            ->delete('words')
            ->where('id', '?')
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute([$wordId]);

        return $query->rowCount();
    }

    public function updateWord(int $id, string $text): int
    {
        $queryString = $this->sqlQueryBuilder
            ->update('words', ['text'])
            ->where('id', ':id')
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute(['text' => $text, 'id' => $id]);

        return $query->rowCount();
    }

    public function insertWord(string $word): Word
    {
        $queryString = $this->sqlQueryBuilder
            ->insert('words', ['text'])
            ->values(['(:words_text)'])
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute(['words_text' => $word]);

        $id = $this->connection->lastInsertId();

        return new Word((int) $id, $word);
    }

    public function insertManyWords(array $words): void
    {
        if (empty($words)) {
            return;
        }

        $placeholders = rtrim(str_repeat('(?), ', count($words)), ', ');

        $queryString = $this->sqlQueryBuilder
            ->insert('words', ['text'])
            ->values([$placeholders])
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute($words);
    }

    public function getWordById(int $id): ?Word
    {
        $queryString = $this->sqlQueryBuilder
            ->select('words', ['*'])
            ->where('id', ':id')
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute(['id' => $id]);

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return new Word($result['id'], $result['text']);
    }

    public function findWordByText(string $text): ?Word
    {
        $queryString = $this->sqlQueryBuilder
            ->select('words', ['*'])
            ->where('text', ':text')
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute(['text' => $text]);

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return new Word((int) $result['id'], $result['text']);
    }

    public function clearWordTable(): void
    {
        $queryString = $this->sqlQueryBuilder
            ->delete('words')
            ->getSql();

        $query = $this->connection->prepare($queryString);
        $query->execute();
    }
}
