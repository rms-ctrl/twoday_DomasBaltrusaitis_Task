<?php

declare(strict_types=1);

namespace Tests\HyphenationTests;

use App\Entities\HyphenatedWord;
use App\Entities\SelectedSyllable;
use App\Entities\Word;
use App\Repositories\HyphenatedWordRepository;
use App\Repositories\SyllableRepository;
use App\Repositories\WordRepository;
use App\Services\DatabaseHyphenationManagementService;
use App\Services\ParagraphHyphenationService;
use App\Services\TransactionService;
use PHPUnit\Framework\TestCase;

class DatabaseHyphenationManagementServiceTest extends TestCase
{
    public function testManageHyphenationWhenBothWordsExist(): void
    {
        $wordRepositoryMock = $this->createMock(WordRepository::class);
        $wordRepositoryMock
            ->method('findWordByText')
            ->willReturn(new Word(1, 'Testuojamas'));

        $hyphenatedWordRepositoryMock = $this->createMock(HyphenatedWordRepository::class);
        $hyphenatedWordRepositoryMock
            ->method('findHyphenatedWordById')
            ->willReturn(new HyphenatedWord(1, 'Tes-tuo-ja-mas', 1));

        $syllableRepositoryMock = $this->createMock(SyllableRepository::class);
        $syllableRepositoryMock
            ->method('getAllSyllablesByHyphenatedWordId')
            ->willReturn([
                new SelectedSyllable(1, 'Tes'),
                new SelectedSyllable(2, 'tuo'),
                new SelectedSyllable(3, 'ja'),
                new SelectedSyllable(4, 'mas')
            ]);

        $transactionServiceMock = $this->createMock(TransactionService::class);
        $paragraphHyphenationServiceMock = $this->createMock(ParagraphHyphenationService::class);

        $databaseHyphenationManagementService = new DatabaseHyphenationManagementService($transactionServiceMock, $paragraphHyphenationServiceMock, $wordRepositoryMock, $syllableRepositoryMock, $hyphenatedWordRepositoryMock);

        $data = $databaseHyphenationManagementService->manageHyphenation(['Testuojamas']);

        $this->assertEquals(
            [
                0 => [
                    'hyphenated_word' => new HyphenatedWord(1, 'Tes-tuo-ja-mas', 1),
                    'syllables' => [
                        new SelectedSyllable(1, 'Tes'),
                        new SelectedSyllable(2, 'tuo'),
                        new SelectedSyllable(3, 'ja'),
                        new SelectedSyllable(4, 'mas')
                    ]
                ]
            ], $data);
    }
}
