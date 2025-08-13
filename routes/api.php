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
    $app->post('/event', function (Request $request, Response $response) use ($app) {
        $data = Json::getJsonBody($request);
        $type = $data['type'] ?? null;

        $container = $app->getContainer();
        $accountController = $container->get(\App\Controllers\AccountController::class);


        if ($type === EventEnum::ACCOUNT_DEPOSIT) {
            return $accountController->deposit($request, $response);
        }

        return Json::jsonResponse($response,
            ['error' => 'Event type not found or unsupported'],
            HttpCodeEnum::NOT_FOUND
        );
    });
};
