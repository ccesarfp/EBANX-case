<?php

use App\Controllers\AccountController;
use App\Repositories\InMemoryAccountRepository;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\ResetMemoryRepositoryInterface;
use App\Services\AccountService;
use App\Services\Interfaces\AccountServiceInterface;
use App\Services\Interfaces\ResetMemoryServiceInterface;
use App\Services\MemoryService;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

use function DI\autowire;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    AccountRepositoryInterface::class => autowire(InMemoryAccountRepository::class),
    AccountServiceInterface::class => autowire(AccountService::class),
    ResetMemoryRepositoryInterface::class => autowire(InMemoryAccountRepository::class),
    ResetMemoryServiceInterface::class => autowire(MemoryService::class),
    AccountController::class => autowire(\App\Controllers\AccountController::class),
]);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$routeDefinition = require __DIR__ . '/../routes/api.php';
$routeDefinition($app);

return $app;
