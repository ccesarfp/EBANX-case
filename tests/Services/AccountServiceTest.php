<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Services\AccountService;
use App\Services\Interfaces\AccountServiceInterface;
use InvalidArgumentException;

class AccountServiceTest extends TestCase
{
    private AccountServiceInterface $service;
    private $repositoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(AccountRepositoryInterface::class);
        $this->service = new AccountService($this->repositoryMock);
    }

    public function testCreateAccountSuccess()
    {
        $accountId = 1;

        $this->repositoryMock
            ->expects($this->once())
            ->method('createAccount')
            ->with($accountId);

        $this->service->createAccount($accountId);
    }

    public function testCreateAccountThrowsAccountAlreadyExistsException()
    {
        $accountId = 1;

        $this->repositoryMock
            ->method('createAccount')
            ->willThrowException(new AccountAlreadyExistsException());

        $this->expectException(AccountAlreadyExistsException::class);
        $this->service->createAccount($accountId);
    }

    public function testCreateAccountThrowsInvalidArgumentException()
    {
        $accountId = -5;

        $this->repositoryMock
            ->method('createAccount')
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(InvalidArgumentException::class);
        $this->service->createAccount($accountId);
    }

    public function testDepositSuccess()
    {
        $accountId = 1;
        $amount = 100.0;
        $newBalance = 150.0;

        $this->repositoryMock
            ->expects($this->once())
            ->method('deposit')
            ->with($accountId, $amount)
            ->willReturn($newBalance);

        $result = $this->service->deposit($accountId, $amount);

        $this->assertEquals($newBalance, $result);
    }

    public function testDepositCreatesAccountIfNotFound()
    {
        $accountId = 1;
        $amount = 50.0;
        $newBalance = 50.0;

        $this->repositoryMock
            ->expects($this->exactly(2))
            ->method('deposit')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new AccountNotFoundException()),
                $newBalance
            );

        $this->repositoryMock
            ->expects($this->once())
            ->method('createAccount')
            ->with($accountId);

        $result = $this->service->deposit($accountId, $amount);

        $this->assertEquals($newBalance, $result);
    }

    public function testDepositThrowsInvalidAmountException()
    {
        $accountId = 1;
        $amount = -10;

        $this->repositoryMock
            ->method('deposit')
            ->willThrowException(new InvalidAmountException());

        $this->expectException(InvalidAmountException::class);
        $this->service->deposit($accountId, $amount);
    }

    public function testDepositThrowsInvalidArgumentException()
    {
        $accountId = -1;
        $amount = 10;

        $this->repositoryMock
            ->method('deposit')
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(InvalidArgumentException::class);
        $this->service->deposit($accountId, $amount);
    }
}
