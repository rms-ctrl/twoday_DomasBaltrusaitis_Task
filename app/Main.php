<?php

declare(strict_types=1);

namespace App;

require_once 'Autoloader.php';

use App\Database\DBConnection;
use App\Database\QueryBuilder\MySqlQueryBuilder;
use App\Database\QueryBuilder\SqlQueryBuilder;
use App\Enumerators\AppType;
use App\Exception\NotFoundException;
use App\Logger\Handler\LogHandler;
use App\Logger\Logger;
use App\Providers\CliWordProvider;
use App\Providers\DatabaseSyllableProvider;
use App\Providers\DatabaseWordProvider;
use App\Providers\FileSyllableProvider;
use App\Providers\FileWordProvider;
use App\Repositories\HyphenatedWordRepository;
use App\Repositories\SelectedSyllableRepository;
use App\Repositories\SyllableRepository;
use App\Repositories\WordRepository;
use App\Services\BasicHyphenationManagementService;
use App\Services\DatabaseHyphenationManagementService;
use App\Services\FileService;
use App\Services\HyphenationService;
use App\Services\ParagraphHyphenationService;
use App\Services\ResultVisualizationService;
use App\Services\TransactionService;
use App\Services\UserInputService;
use App\Utilities\Timer;

class Main
{
    public function run(array $argv = []): void
    {
        if (count($argv) <= 1 || !isset($argv[2]) || !file_exists(__DIR__ . $argv[2])) {
            throw new \Exception(\InvalidArgumentException::class);
        }

        $loader = new Autoloader();
        $loader->register();
        $loader->addNamespace('App', __DIR__);

        FileService::readEnvFile('/var/.env');
        // ---------------
        $dbConnection = DBConnection::tryConnect();

        $queryBuilder = new MySqlQueryBuilder();

        $query = $queryBuilder
            ->select('words', ['words.text, words.id'])
            ->leftJoin('hyphenationdb.hyphenated_words hw', 'words.id = hw.word_id')
            ->where('hw.word_id', 'IS NULL')
            ->getSql();

        $query = $dbConnection->prepare($query);
        $query->execute();
        $wordRows = $query->fetchAll(\PDO::FETCH_ASSOC);

        echo $wordRows;
        // -----------------
        $timer = new Timer();
        $handler = new LogHandler('/var/app_log.txt');
        $logger = new Logger($handler);
        $wordRepository = new WordRepository($dbConnection, $logger);
        $selectedSyllableRepository = new SelectedSyllableRepository($dbConnection);
        $hyphenatedWordRepository = new HyphenatedWordRepository($dbConnection);
        $syllableRepository = new SyllableRepository($dbConnection, $logger);
        $userInputService = new UserInputService($wordRepository, $syllableRepository);
        $resultVisualizationService = new ResultVisualizationService($logger);

        $applicationType = $userInputService->checkUserArgInput($argv[1]);
        $userInputService->askAboutDatabaseFileUpdates();
        $isDbSource = $userInputService->chooseHyphenationSource();

        $logger->logStartOfApp();
        $timer->startTimer();

        if ($applicationType === AppType::DATABASE) {
            $transactionService = new TransactionService($hyphenatedWordRepository, $syllableRepository, $selectedSyllableRepository, $dbConnection);
            $words = (new DatabaseWordProvider($wordRepository))->getWords();
            $syllables = (new DatabaseSyllableProvider($syllableRepository))->getSyllables();

            $dbHyphenationManagementService = new DatabaseHyphenationManagementService(
                $transactionService,
                new ParagraphHyphenationService(new HyphenationService($syllables)),
                $wordRepository,
                $syllableRepository,
                $hyphenatedWordRepository,
            );

            $result = $dbHyphenationManagementService->manageHyphenation($words);
        } else {
            $words = $applicationType === AppType::FILE
                ? (new FileWordProvider('/var/paragraph.txt'))->getWords()
                : (new CliWordProvider($userInputService))->getWords();

            $syllables = $isDbSource
                ? (new DatabaseSyllableProvider($syllableRepository))->getSyllables()
                : (new FileSyllableProvider())->getSyllables();

            $basicHyphenationManagementService = new BasicHyphenationManagementService(
                new ParagraphHyphenationService(new HyphenationService($syllables))
            );

            $result = $basicHyphenationManagementService->manageHyphenation($words);
        }

        foreach ($result as $data) {
            $resultVisualizationService->visualizeString($data['hyphenated_word']->getText());

            if ($applicationType === AppType::WORD) {
                $resultVisualizationService->visualizeSelectedSyllables($data['syllables']);
            }
        }

        $timer->endTimer();
        $timeSpent = $timer->getTimeSpent();
        $resultVisualizationService->VisualizeString("<< Time spent {$timeSpent} seconds >>\n");
        $logger->logEndOfApp();
    }
}

$app = new Main();
//$app->run($argv);
$app->run([
    1 => 'word',
    2 => '/var/paragraph.txt',
]);
