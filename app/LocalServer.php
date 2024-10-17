<?php

declare(strict_types=1);

namespace App;

require_once __DIR__.'/../vendor/autoload.php';

use App\Container\DependencyConfigurator;
use App\Container\DependencyContainer;
use App\Enumerators\HttpMethods;
use App\Exception\HttpException;
use App\Routes\Route;
use App\Routes\RouteManager;
use App\Services\FileService;

class LocalServer
{
    public function run(): void
    {
        FileService::readEnvFile('/Server/var/.env');

        $container = new DependencyContainer();
        DependencyConfigurator::setAllDependencies($container);

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $wordController = $container->get('wordController');

        $wordRoutes = [
            new Route(HttpMethods::GET, '/words', $wordController, 'getAll'),
            new Route(HttpMethods::GET, '/words/{id}', $wordController, 'getById'),
            new Route(HttpMethods::POST, '/words', $wordController, 'create'),
            new Route(HttpMethods::PUT, '/words/{id}', $wordController, 'update'),
            new Route(HttpMethods::DELETE, '/words/{id}', $wordController, 'delete'),
        ];

        $routeManager = new RouteManager($wordRoutes);

        try {
            header("Content-Type: application/json; charset=UTF-8");

            $response = $routeManager->processRequest($uri, $_SERVER['REQUEST_METHOD']);
            http_response_code($response->getResponseCode());

            echo $response->getBody();
        } catch (HttpException $exception) {
            http_response_code($exception->getResponseCode());

            echo json_encode($exception->getMessage());
        }
    }
}

$localServer = new LocalServer();
$localServer->run();
