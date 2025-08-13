<?php

use App\Controllers\AccountController;
use App\Enums\ContentTypeEnum;
use App\Enums\EventEnum;
use App\Enums\HttpCodeEnum;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\Json;

return function (App $app) {
    $app->post('/reset', \App\Controllers\AccountController::class . ":reset");
    $app->get('/balance', \App\Controllers\AccountController::class . ":getBalance");
    $app->post('/event', \App\Controllers\AccountController::class . ":event");
};
