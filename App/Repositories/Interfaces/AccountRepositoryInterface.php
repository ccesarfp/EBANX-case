<?php

namespace App\Repositories\Interfaces;

interface AccountRepositoryInterface
{
    /**
     * Deposits a specific amount into an account.
     * If the account doesn't exist, it will be created.
     *
     * @param string $accountId The account ID where the deposit will be made.
     * @param float|int $amount   The amount to deposit.
     * @return float Returns the new balance of the account.
     *
     * @throws \App\Exceptions\InvalidAmountException If the amount is invalid.
     */
    public function deposit(string $accountId, float|int $amount): float;
}
