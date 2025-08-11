<?php

use App\Exceptions\AccountAlreadyExistsException;
use PHPUnit\Framework\TestCase;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Repositories\InMemoryAccountRepository;

class InMemoryAccountRepositoryTest extends TestCase
{
    private InMemoryAccountRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAccountRepository();
    }

    public function testCreateAccountInitializesWithZeroBalance()
    {
        $accountId = 1;
        $this->repository->createAccount($accountId);

        $balance = $this->repository->deposit($accountId, 1);
        $this->assertEquals(1.0, $balance);
    }

    public function testCreateAccountInitializesWithInvalidAccountId()
    {
        $accountId = -1;
        $this->expectException(InvalidArgumentException::class);
        $this->repository->createAccount($accountId);
    }

    public function testDuplicateCreateAccount()
    {
        $accountId = 1;
        $this->repository->createAccount($accountId);
        $this->expectException(AccountAlreadyExistsException::class);
        $this->repository->createAccount($accountId);
    }

    public function testDepositIncreasesBalance()
    {
        $accountId = 2;
        $this->repository->createAccount($accountId);
        $this->repository->deposit($accountId, 50);
        $balance = $this->repository->deposit($accountId, 25);

        $this->assertEquals(75.0, $balance);
    }

    public function testDepositIncreasesBalanceWithInvalidAmount()
    {
        $accountId = 1;
        $this->repository->createAccount($accountId);

        $this->expectException(InvalidArgumentException::class);
        $this->repository->deposit($accountId, -1);
    }

    public function testDepositThrowsExceptionForInvalidAmount()
    {
        $this->expectException(InvalidAmountException::class);
        $accountId = 3;
        $this->repository->createAccount($accountId);
        $this->repository->deposit($accountId, 0);
    }

    public function testDepositThrowsExceptionForNonExistentAccount()
    {
        $this->expectException(AccountNotFoundException::class);
        $this->repository->deposit(999, 10);
    }

}
