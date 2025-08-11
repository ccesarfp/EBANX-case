<?php

use App\Controllers\AccountController;
use App\Enums\ContentTypeEnum;
use App\Enums\EventEnum;
use App\Enums\HttpCodeEnum;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $app->post('/event', function (Request $request, Response $response) use ($app) {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $type = $data['type'] ?? null;

        $container = $app->getContainer();
        $accountController = $container->get(\App\Controllers\AccountController::class);


        if ($type === EventEnum::ACCOUNT_DEPOSIT) {
            return $accountController->deposit($request, $response);
        }

        $response->getBody()->write(json_encode(['error' => 'Event type not found or unsupported']));
        return $response
            ->withStatus(HttpCodeEnum::NOT_FOUND)
            ->withHeader('Content-Type', ContentTypeEnum::JSON);
    });
};
