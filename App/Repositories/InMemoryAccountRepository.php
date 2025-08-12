<?php

namespace App\Repositories;

use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Repositories\Interfaces\ResetMemoryRepositoryInterface;

class InMemoryAccountRepository implements AccountRepositoryInterface, ResetMemoryRepositoryInterface {
    private array $accounts = [];
    private string $storageFile;

    public function __construct()
    {
        $this->storageFile = sys_get_temp_dir() . '/accounts.json';
        if (file_exists($this->storageFile)) {
            $this->accounts = json_decode(file_get_contents($this->storageFile), true) ?? [];
        }
    }

    private function persist()
    {
        file_put_contents($this->storageFile, json_encode($this->accounts));
    }

    public function resetMemory(): bool {
        $this->accounts = [];

        $deleted = true;
        if (!empty($this->storageFile) && file_exists($this->storageFile)) {
            $deleted = unlink($this->storageFile);
        }

        return $deleted;
    }

    public function createAccount(int $accountId): void {
        if (isset($this->accounts[$accountId])) {
            throw new AccountAlreadyExistsException("Account with ID {$accountId} already exists.");
        }
        $this->accounts[$accountId] = 0.0;
        $this->persist();
    }

    public function deposit(int $accountId, float $amount): float
    {
        if (!isset($this->accounts[$accountId])) {
            throw new AccountNotFoundException("Account with ID {$accountId} does not exist.");
        }
        $this->accounts[$accountId] += $amount;
        $this->persist();
        return $this->accounts[$accountId];
    }
}
