<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Services\AccountService;
use App\Services\Interfaces\AccountServiceInterface;

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

    public function testGetAccountBalanceSuccess()
    {
        $accountId = 1;
        $amount = 100.0;

        $this->repositoryMock
            ->expects($this->once())
            ->method('getAccountBalance')
            ->with($accountId)
            ->willReturn($amount);

        $result = $this->service->getAccountBalance($accountId);
        $this->assertEquals($amount, $result);
    }

    public function testGetAccountBalanceThrowsInvalidArgumentException()
    {
        $accountId = -1;

        $this->expectException(InvalidArgumentException::class);
        $this->service->getAccountBalance($accountId);
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

    public function testWithdrawSuccess()
    {
        $accountId = 301;
        $amount = 50.0;
        $newBalance = 0.0;

        $this->repositoryMock
            ->expects($this->once())
            ->method('getAccountBalance')
            ->with($accountId)
            ->willReturn($amount);

        $this->repositoryMock
            ->expects($this->once())
            ->method('withdraw')
            ->with($accountId, $amount)
            ->willReturn($newBalance);

        $result = $this->service->withdraw($accountId, $amount);

        $this->assertEquals($newBalance, $result);
    }

    public function testWithdrawThrowsInvalidArgumentException()
    {
        $accountId = -1;
        $amount = 10;

        $this->expectException(InvalidArgumentException::class);
        $this->service->withdraw($accountId, $amount);
    }

    public function testWithdrawThrowsInvalidAmountException()
    {
        $accountId = 1;
        $amount = 100.0;
        $currentBalance = 50.0;

        $this->repositoryMock
            ->method('getAccountBalance')
            ->with($accountId)
            ->willReturn($currentBalance);

        $this->expectException(InvalidAmountException::class);
        $this->service->withdraw($accountId, $amount);
    }

    public function testTransferSuccess()
    {
        $origin = 1;
        $destination = 2;
        $amount = 50.0;
        $newBalances = ['origin' => 50.0, 'destination' => 50.0];

        $this->repositoryMock
            ->expects($this->exactly(1))
            ->method('getAccountBalance')
            ->willReturnOnConsecutiveCalls(100.0);

        $this->repositoryMock
            ->expects($this->once())
            ->method('withdraw')
            ->with($origin, $amount)
            ->willReturn($newBalances['origin']);

        $this->repositoryMock
            ->expects($this->once())
            ->method('deposit')
            ->with($destination, $amount)
            ->willReturn($newBalances['destination']);

        $result = $this->service->transfer($origin, $destination, $amount);

        $this->assertEquals($newBalances, $result);
    }

    public function testTransferThrowsInvalidArgumentException()
    {
        $origin = -1;
        $destination = 2;
        $amount = 50.0;

        $this->expectException(InvalidArgumentException::class);
        $this->service->transfer($origin, $destination, $amount);
    }

    public function testTransferThrowsAccountNotFoundException()
    {
        $origin = 1;
        $destination = 2;
        $amount = 50.0;

        $this->repositoryMock
            ->method('getAccountBalance')
            ->willThrowException(new AccountNotFoundException());

        $this->expectException(AccountNotFoundException::class);
        $this->service->transfer($origin, $destination, $amount);
    }

    public function testTransferThrowsInvalidAmountException()
    {
        $origin = 1;
        $destination = 2;
        $amount = -50.0;

        $this->expectException(InvalidAmountException::class);
        $this->service->transfer($origin, $destination, $amount);
    }

    public function testTransferThrowsInsufficientFundsException()
    {
        $origin = 1;
        $destination = 2;
        $amount = 100.0;

        $this->repositoryMock
            ->method('getAccountBalance')
            ->with($origin)
            ->willReturn(50.0);

        $this->expectException(InvalidAmountException::class);
        $this->service->transfer($origin, $destination, $amount);
    }
}
