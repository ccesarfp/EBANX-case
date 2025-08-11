<?php

use App\Controllers\AccountController;
use App\Repositories\InMemoryAccountRepository;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Services\AccountService;
use App\Services\Interfaces\AccountServiceInterface;
use Slim\Factory\AppFactory;
use DI\Container;
use DI\ContainerBuilder;

use function DI\autowire;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    AccountRepositoryInterface::class => autowire(InMemoryAccountRepository::class),
    AccountServiceInterface::class => autowire(AccountService::class),
    AccountController::class => autowire(\App\Controllers\AccountController::class),
]);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$routeDefinition = require __DIR__ . '/../routes/api.php';
$routeDefinition($app);

return $app;
