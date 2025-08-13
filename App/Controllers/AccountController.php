<?php

namespace App\Controllers;

use App\Enums\HttpCodeEnum;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Services\Interfaces\AccountServiceInterface;
use App\Services\Interfaces\ResetMemoryServiceInterface;
use InvalidArgumentException;
use App\Utils\Json;

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

        return Json::jsonResponse($response, 'OK', $status);
    }

    /**
     * Get the balance of an account.
     *
     * @throws AccountNotFoundException
     */
    public function getBalance(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $accountId = filter_var($queryParams['account_id'] ?? null, FILTER_VALIDATE_INT);

        if ($accountId === null) {
            return Json::jsonResponse($response, ['error' => 'Invalid account ID'], HttpCodeEnum::BAD_REQUEST);
        }

        try {
            $balance = $this->accountService->getAccountBalance($accountId);
            return Json::jsonResponse($response, $balance, HttpCodeEnum::OK);

        } catch (AccountNotFoundException $e) {
            $responseData = 0;
            $status = HttpCodeEnum::NOT_FOUND;
        } catch (InvalidArgumentException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::BAD_REQUEST;
        }

        return Json::jsonResponse($response, $responseData, $status);
    }

    /**
     * Deposit amount into an account.
     */
    public function deposit(Request $request, Response $response): Response
    {
        $data = Json::getJsonBody($request);

        $destination = filter_var($data['destination'] ?? null, FILTER_VALIDATE_INT);
        $amount = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);


        if ($destination === false || $amount === false) {
            return Json::jsonResponse($response, ['error' => 'Missing destination or amount'], HttpCodeEnum::BAD_REQUEST);
        }

        try {
            $newBalance = $this->accountService->deposit((int)$destination, (float)$amount);

            return Json::jsonResponse($response, [
                'destination' => [
                    'id' => (string)$destination,
                    'balance' => $newBalance
                ]
            ], HttpCodeEnum::CREATED);

        } catch (AccountNotFoundException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::NOT_FOUND;
        } catch (InvalidAmountException | InvalidArgumentException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::BAD_REQUEST;
        }

        return Json::jsonResponse($response, $responseData, $status);
    }
}
