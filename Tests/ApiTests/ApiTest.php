<?php

declare(strict_types=1);

namespace Tests\ApiTests;
use App\Database\DatabaseConnection;
use App\Services\FileService;
use PHPUnit\Framework\TestCase;

abstract class ApiTest extends TestCase
{
    abstract protected function testGet(int $id, string $expectedValue): void;
    abstract protected function testGetAll(): void;

    protected static function setupEnvironmentAndConnection(): \PDO
    {
        FileService::readEnvFile('/Server/var/.env');

        return DatabaseConnection::tryConnect();
    }

    protected function makeGetRequest(string $uri): mixed
    {
        $context_options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Content-Type' => 'application/json',
                ]
            ]
        ];

        $context = stream_context_create($context_options);
        $stream = fopen($uri, 'r', false, $context);

        return json_decode(stream_get_contents($stream), true);
    }
}
