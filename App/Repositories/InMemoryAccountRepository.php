<?php

namespace App\Repositories;

use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Repositories\Interfaces\AccountRepositoryInterface;

class InMemoryAccountRepository implements AccountRepositoryInterface {
    /**
     * @var array<int, float> Stores account balances in memory
     */
    private array $accounts = [];

    public function createAccount(int $accountId): void {
        if (isset($this->accounts[$accountId])) {
            throw new AccountAlreadyExistsException("Account with ID {$accountId} already exists.");
        }

        $this->accounts[$accountId] = 0.0;
    }

    public function deposit(int $accountId, float $amount): float
    {
        if (!isset($this->accounts[$accountId])) {
            throw new AccountNotFoundException("Account with ID {$accountId} does not exist.");
        }

        $this->accounts[$accountId] += $amount;

        return $this->accounts[$accountId];
    }
}
