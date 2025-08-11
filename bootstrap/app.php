<?php

use Slim\Factory\AppFactory;
use DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container();

AppFactory::setContainer($container);
$app = AppFactory::create();

$routeDefinition = require __DIR__ . '/../routes/api.php';
$routeDefinition($app);

return $app;
