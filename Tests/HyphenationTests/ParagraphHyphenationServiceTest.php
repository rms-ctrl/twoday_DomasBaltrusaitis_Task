<?php

declare(strict_types=1);

namespace Tests\HyphenationTests;

use App\Services\HyphenationService;
use App\Services\ParagraphHyphenationService;
use PHPUnit\Framework\TestCase;

class ParagraphHyphenationServiceTest extends TestCase
{
    public function testHyphenateParagraph(): void
    {
        $hyphenationServiceMock = $this->createMock(HyphenationService::class);

        $hyphenationServiceMock
            ->expects($this->exactly(2))
            ->method('hyphenateWords')
            ->willReturn(['mis-trans-late'], ['re-turn']);

        $paragraphHyphenationService = new ParagraphHyphenationService($hyphenationServiceMock);

        $this->assertEquals('mis-trans-late', $paragraphHyphenationService->hyphenateParagraph('mistranslate'));
        $this->assertEquals('re-turn', $paragraphHyphenationService->hyphenateParagraph('return'));
    }
}
