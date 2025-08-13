<?php

namespace App\Controllers;

use App\Enums\EventEnum;
use App\Enums\HttpCodeEnum;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Exceptions\MissingValueException;
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

        if ($accountId === null || $accountId === false) {
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
     * Handle events.
     *
     * @throws MissingValueException
     * @throws AccountNotFoundException
     * @throws InvalidAmountException
     */
    public function event(Request $request, Response $response) {
        $data = Json::getJsonBody($request);
        $type = $data['type'] ?? null;

        if ($type === null) {
            return Json::jsonResponse($response, ['error' => 'Event type not found or unsupported'], HttpCodeEnum::NOT_FOUND);
        }

        $responseData = [];
        $status = null;
        try {
            $amount = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);
            switch ($type) {
                case EventEnum::ACCOUNT_DEPOSIT:
                    $destination = filter_var($data['destination'] ?? null, FILTER_VALIDATE_INT);

                    $result = $this->deposit($destination, $amount);
                    break;
                case EventEnum::ACCOUNT_WITHDRAW:
                    $origin = filter_var($data['origin'] ?? null, FILTER_VALIDATE_INT);

                    $result = $this->withdraw($origin, $amount);
                    break;
                case EventEnum::ACCOUNT_TRANSFER:
                    $destination = filter_var($data['destination'] ?? null, FILTER_VALIDATE_INT);
                    $origin = filter_var($data['origin'] ?? null, FILTER_VALIDATE_INT);

                    $result = $this->transfer($origin, $destination, $amount);
                    break;
                default:
                    throw new InvalidArgumentException("Unsupported event type: {$type}");
            }

            return Json::jsonResponse($response, $result, HttpCodeEnum::CREATED);
        } catch (MissingValueException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::BAD_REQUEST;
        } catch (AccountNotFoundException $e) {
            $responseData = 0;
            $status = HttpCodeEnum::NOT_FOUND;
        } catch (InvalidAmountException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::BAD_REQUEST;
        } catch (InvalidArgumentException $e) {
            $responseData = ['error' => $e->getMessage()];
            $status = HttpCodeEnum::NOT_FOUND;
        }

        return Json::jsonResponse($response, $responseData, $status);
    }

    /**
     * Deposit amount into an account.
     */
    private function deposit(int $destination, float $amount): array
    {
        if ($destination === null || $destination === false || $destination <= 0) {
            throw new MissingValueException("Missing destination account ID.");
        }

        if ($amount === null || $amount === false || $amount <= 0) {
            throw new MissingValueException("Missing amount.");
        }

        $newBalance = $this->accountService->deposit((int)$destination, (float)$amount);

        return [
            'destination' => [
                'id' => (string)$destination,
                'balance' => $newBalance
            ]
        ];
    }

    /**
     * Withdraw amount from an account.
     */
    private function withdraw(int $origin, float $amount): array
    {
        if ($origin === null || $origin === false || $origin <= 0) {
            throw new MissingValueException("Missing origin account ID.");
        }

        if ($amount === null || $amount === false || $amount <= 0) {
            throw new MissingValueException("Missing amount.");
        }

        $newBalance = $this->accountService->withdraw((int)$origin, (float)$amount);

        return [
            'origin' => [
                'id' => (string)$origin,
                'balance' => $newBalance
            ]
        ];
    }

    /**
     * Transfer amount from one account to another.
     */
    private function transfer(int $origin, int $destination, float $amount): array
    {
        if ($origin === null || $origin === false || $origin <= 0) {
            throw new MissingValueException("Missing origin account ID.");
        }
        if ($destination === null || $destination === false || $destination <= 0) {
            throw new MissingValueException("Missing destination account ID.");
        }
        if ($amount === null || $amount === false || $amount <= 0) {
            throw new MissingValueException("Missing amount.");
        }

        $transferData = $this->accountService->transfer((int)$origin, (int)$destination, (float)$amount);

        return [
            'origin' => [
                'id' => (string)$origin,
                'balance' => $transferData['origin']
            ],
            'destination' => [
                'id' => (string)$destination,
                'balance' => $transferData['destination']
            ]
        ];
    }
}
