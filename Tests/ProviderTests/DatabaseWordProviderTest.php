<?php

declare(strict_types=1);

namespace Tests\ProviderTests;

use App\Providers\DatabaseWordProvider;
use App\Repositories\WordRepository;
use PHPUnit\Framework\TestCase;

class DatabaseWordProviderTest extends TestCase
{
    public function testGetWords(): void
    {
        $wordRepositoryMock = $this->createMock(WordRepository::class);

        $wordRepositoryMock
            ->method('getAllWords')
            ->with(true)
            ->willReturn(['mistranslate', 'return']);

        $provider = new DatabaseWordProvider($wordRepositoryMock);

        $words = $provider->getWords();

        $this->assertEquals(['mistranslate', 'return'], $words);
    }
}
