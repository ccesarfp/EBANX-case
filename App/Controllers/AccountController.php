<?php

namespace App\Controllers;

use App\Enums\ContentTypeEnum;
use App\Enums\HttpCodeEnum;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Services\Interfaces\AccountServiceInterface;
use App\Services\Interfaces\ResetMemoryServiceInterface;
use InvalidArgumentException;

class AccountController
{
    private AccountServiceInterface $accountService;
    private ResetMemoryServiceInterface $memoryService;

    public function __construct(
        AccountServiceInterface $accountService,
        ResetMemoryServiceInterface $memoryService
    )
    {
        $this->accountService = $accountService;
        $this->memoryService = $memoryService;
    }

    /**
     * Reset all accounts in memory.
     */
    public function reset(Request $request, Response $response): Response
    {
        $deleted = $this->memoryService->resetMemory();
        $status = $deleted ? HttpCodeEnum::OK : HttpCodeEnum::INTERNAL_SERVER_ERROR;

        $response->getBody()->write("OK");
        return $response->withStatus($status)->withHeader('Content-Type', ContentTypeEnum::JSON);
    }

    /**
     * Deposit amount into an account.
     */
    public function deposit(Request $request, Response $response): Response
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

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
                    'id' => (string)$destination,
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
