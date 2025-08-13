<?php

namespace App\Utils;

use App\Enums\ContentTypeEnum;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Json
{
    public static function jsonResponse(Response $response, array|string $data, int $status): Response {
        $response
            ->getBody()
            ->write(is_array($data) ? json_encode($data, JSON_THROW_ON_ERROR) : $data);
        return $response
                ->withStatus($status)
                ->withHeader('Content-Type', ContentTypeEnum::JSON);
    }

    public static function getJsonBody(Request $request): array
    {
        $body = (string)$request->getBody();
        return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }
}
