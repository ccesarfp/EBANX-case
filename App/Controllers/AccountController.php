<?php

namespace App\Controllers;

use App\Enums\ContentTypeEnum;
use App\Enums\HttpCodeEnum;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Services\Interfaces\AccountServiceInterface;
use InvalidArgumentException;

class AccountController
{
    private AccountServiceInterface $accountService;

    public function __construct(AccountServiceInterface $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Deposit amount into an account.
     */
    public function deposit(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $destination = filter_var($data['destination'] ?? null, FILTER_VALIDATE_INT);
        $amount = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);


        if ($destination === false || $amount === false) {
            $response->getBody()->write(json_encode(['error' => 'Missing destination or amount'], JSON_THROW_ON_ERROR));

            return $response->withStatus(HttpCodeEnum::BAD_REQUEST)->withHeader('Content-Type', ContentTypeEnum::JSON);
        }

        $responseData = [];
        try {
            $newBalance = $this->accountService->deposit((int)$destination, (float)$amount);

            $responseData = [
                'destination' =>[
                    'id' => (int)$destination,
                    'balance' => $newBalance
                ],

            ];
            $status = HttpCodeEnum::CREATED;

        } catch (AccountNotFoundException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::NOT_FOUND;
        } catch (InvalidAmountException | InvalidArgumentException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::BAD_REQUEST;
        }

        $response->getBody()->write(json_encode($responseData, JSON_THROW_ON_ERROR));
        return $response->withStatus($status)->withHeader('Content-Type', ContentTypeEnum::JSON);
    }
}
