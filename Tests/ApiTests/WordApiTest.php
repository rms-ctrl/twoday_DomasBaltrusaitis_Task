<?php

declare(strict_types=1);

namespace Tests\ApiTests;

use App\Database\QueryBuilder\MySqlQueryBuilder;
use App\Repositories\WordRepository;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class WordApiTest extends ApiTest
{
    private static \PDO $connection;
    private static WordRepository $wordRepository;

    public function setUp(): void
    {
        $data = [
            [
                'id' => 1,
                'text' => 'testword1'
            ],
            [
                'id' => 2,
                'text' => 'testword2'
            ]
        ];

        foreach ($data as $word) {
            self::$wordRepository->insertWord($word['text']);
        }
    }

    public static function setUpBeforeClass(): void
    {
        self::$connection = self::setupEnvironmentAndConnection();

        $queryBuilder = new MySqlQueryBuilder();
        self::$wordRepository = new WordRepository($queryBuilder, self::$connection);
    }

    protected function tearDown(): void
    {
        $this->truncateTables();
    }

    public function testGetAll(): void
    {
        $uri = 'http://127.0.0.1:8000/words';

        $data = $this->makeGetRequest($uri);

        $expectedValues = [
            [
                'id' => 1,
                'text' => 'testword1'
            ],
            [
                'id' => 2,
                'text' => 'testword2'
            ]
        ];

        $this->assertSame($expectedValues, $data);
    }

    #[DataProviderExternal(WordApiDataProvider::class ,'provideTestGet')]
    public function testGet(int $id, string $expectedValue): void
    {
        $uri = "http://127.0.0.1:8000/words/{$id}";

        $data = $this->makeGetRequest($uri);

        $this->assertSame($expectedValue, $data['text']);
    }

    private function truncateTables(): void
    {
        $tables = self::$connection->prepare('SHOW TABLES');
        $tables->execute();

        $this->setForeignKeyChecks(0);

        foreach($tables->fetchAll(\PDO::FETCH_COLUMN) as $table) {
            self::$connection->query('TRUNCATE TABLE `' . $table . '`')->execute();
        }

        $this->setForeignKeyChecks(1);
    }

    private function setForeignKeyChecks(int $flag): void
    {
        self::$connection->query("SET FOREIGN_KEY_CHECKS=$flag;");
    }
}
