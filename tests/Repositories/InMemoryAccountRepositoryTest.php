<?php

use App\Exceptions\AccountAlreadyExistsException;
use PHPUnit\Framework\TestCase;

use App\Exceptions\AccountNotFoundException;
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

    public function testDuplicateCreateAccountThrowsException()
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

    public function testDepositThrowsExceptionForNonExistentAccount()
    {
        $this->expectException(AccountNotFoundException::class);
        $this->repository->deposit(999, 10);
    }
}
