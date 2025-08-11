<?php

namespace App\Services;

use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Services\Interfaces\AccountServiceInterface;
use InvalidArgumentException;

/**
 * Service responsible for account operations.
 */
class AccountService implements AccountServiceInterface
{
    private readonly AccountRepositoryInterface $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * Create a new account with zero balance.
     *
     * @param int $accountId
     * @throws InvalidArgumentException
     * @throws AccountAlreadyExistsException
     */
    public function createAccount(int $accountId): void
    {
        if ($accountId <= 0) {
            throw new InvalidArgumentException("Account ID must be a positive integer.");
        }

        try {
            $this->accountRepository->createAccount($accountId);
        } catch (AccountAlreadyExistsException $e) {
            throw new AccountAlreadyExistsException("Account with ID {$accountId} already exists.");
        }
    }

    /**
     * Deposit an amount into an account.
     * If account does not exist, create it first.
     *
     * @param int $accountId
     * @param float $amount
     * @return float New balance after deposit
     * @throws InvalidArgumentException
     * @throws AccountNotFoundException
     * @throws InvalidAmountException
     */
    public function deposit(int $accountId, float $amount): float
    {
        if ($accountId <= 0) {
            throw new InvalidArgumentException("Account ID must be a positive integer.");
        }

        if ($amount <= 0) {
            throw new InvalidAmountException("Deposit amount must be a positive number.");
        }

        try {
            return $this->accountRepository->deposit($accountId, $amount);
        } catch (AccountNotFoundException $e) {
            $this->accountRepository->createAccount($accountId);
            return $this->accountRepository->deposit($accountId, $amount);
        }
    }
}
