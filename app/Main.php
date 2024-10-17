<?php

declare(strict_types=1);

namespace App;

require_once __DIR__.'/../vendor/autoload.php';

use App\Container\DependencyContainer;
use App\Container\DependencyConfigurator;
use App\Enumerators\AppType;
use App\Services\FileService;


class Main
{
    public function run(array $argv = []): void
    {
        if (count($argv) <= 1 || !isset($argv[2]) || !file_exists(__DIR__ . $argv[2])) {
            throw new \Exception(\InvalidArgumentException::class);
        }

        FileService::readEnvFile('/var/.env');

        $container = new DependencyContainer();

        DependencyConfigurator::setAllDependencies($container);

        $timer = $container->get('timer');
        $logger = $container->get('logger');

        $userInputService = $container->get('userInputService');
        $resultVisualizationService = $container->get('resultVisualizationService');

        $applicationType = $userInputService->checkUserArgInput($argv[1]);
        $userInputService->askAboutDatabaseFileUpdates();
        $isDbSource = $userInputService->chooseHyphenationSource();

        $logger->logStartOfApp();
        $timer->startTimer();

        if ($applicationType === AppType::DATABASE) {
            $words = ($container->get('databaseWordProvider'))->getWords();

            $dbHyphenationManagementService = $container->get('databaseHyphenationManagementService');

            $result = $dbHyphenationManagementService->manageHyphenation($words);
        } else {
            $words = $applicationType === AppType::FILE
                ? ($container->get('fileWordProvider'))->getWords()
                : ($container->get('cliWordProvider'))->getWords();

            $basicHyphenationManagementService = $isDbSource
                ? $container->get('basicHyphenationManagementServiceDB')
                : $container->get('basicHyphenationManagementServiceFile');

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
$app->run($argv);
