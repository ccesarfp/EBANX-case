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
     * Get the balance of an account.
     *
     * @param int $accountId
     * @return float
     * @throws InvalidArgumentException
     */
    public function getAccountBalance(int $accountId): float;

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

    /**
     * Withdraw an amount from an account.
     * If amount is greater than balance, throw an exception.
     * If account does not exist, throw an exception.
     *
     * @param int $accountId
     * @param float $amount
     * @return float New balance after withdrawal
     * @throws InvalidArgumentException
     * @throws AccountNotFoundException
     * @throws InvalidAmountException
     */
    public function withdraw(int $accountId, float $amount): float;

    /**
     * Transfer an amount from one account to another.
     * If either account does not exist, throw an exception.
     *
     * @param int $origin
     * @param int $destination
     * @param float $amount
     * @return array New balances of both accounts
     * @throws InvalidArgumentException
     * @throws AccountNotFoundException
     * @throws InvalidAmountException
     */
    public function transfer(int $origin, int $destination, float $amount): array;
}
