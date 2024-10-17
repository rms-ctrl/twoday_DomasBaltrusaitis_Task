<?php

declare(strict_types=1);

namespace Tests\ApiTests;

class WordApiDataProvider
{
    public static function provideTestGet(): array
    {
        return [
            'First entity to be tested in Get()' => [
                1,
                'testword1'
            ],
            'Second entity to be tested in Get()' => [
                2,
                'testword2'
            ],
        ];
    }
}
