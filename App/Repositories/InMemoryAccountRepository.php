<?php

namespace App\Repositories;

use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use InvalidArgumentException;

class InMemoryAccountRepository implements AccountRepositoryInterface {
    /**
     * @var array<int, float> Stores account balances in memory
     */
    private array $accounts = [];

    public function createAccount(int $accountId): void {
        $this->isValidAccountIdOrFail($accountId);

        if (isset($this->accounts[$accountId])) {
            throw new AccountAlreadyExistsException("Account with ID {$accountId} already exists.");
        }

        $this->accounts[$accountId] = 0.0;
    }

    public function deposit(int $accountId, float $amount): float
    {
        $this->isValidAccountIdOrFail($accountId);
        $this->isAmountOrFail($amount);

        if (!isset($this->accounts[$accountId])) {
            throw new AccountNotFoundException("Account with ID {$accountId} does not exist.");
        }

        $this->accounts[$accountId] += $amount;

        return $this->accounts[$accountId];
    }

    /**
     * Validates the account ID.
     *
     * @throws InvalidArgumentException if the ID is not a positive integer.
     * @param integer $accountId
     * @return void
     */
    private function isValidAccountIdOrFail(int $accountId): void {
        if ($accountId <= 0) {
            throw new InvalidArgumentException("Account ID must be a positive integer.");
        }
    }

    /**
     * Validates the amount.
     *
     * @throws InvalidAmountException if the amount is not a positive number.
     * @param float $amount
     * @return void
     */
    private function isAmountOrFail(float $amount): void {
        if ($amount <= 0) {
            throw new InvalidAmountException("Amount must be a positive number.");
        }
    }
}
