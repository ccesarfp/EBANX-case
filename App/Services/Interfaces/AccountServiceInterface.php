<?php

namespace App\Services\Interfaces;

use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;

interface AccountServiceInterface
{
    /**
     * Create a new account with zero balance.
     *
     * @param int $accountId
     * @throws AccountAlreadyExistsException
     */
    public function createAccount(int $accountId): void;

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
    public function deposit(int $accountId, float $amount): float;
}
