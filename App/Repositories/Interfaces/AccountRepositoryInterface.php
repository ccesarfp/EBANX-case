<?php

namespace App\Repositories\Interfaces;

interface AccountRepositoryInterface
{
    /**
     * Creates a new account with the given accountId.
     * If the account already exists, an exception will be thrown.
     *
     * @param int $accountId The ID for the new account.
     * @return void
     *
     * @throws \App\Exceptions\AccountAlreadyExistsException If the account already exists.
     */
    public function createAccount(int $accountId): void;

    /**
     * Deposits a specific amount into an accountId.
     * If the accountId does not exist, an exception will be thrown.
     *
     * @param int $accountId The account ID where the deposit will be made.
     * @param float $amount The amount to deposit.
     * @return float Returns the new balance of the account.
     *
     * @throws \App\Exceptions\InvalidAmountException If the amount is invalid.
     * @throws \App\Exceptions\AccountNotFoundException If the account does not exist.
     */
    public function deposit(int $accountId, float $amount): float;
}
