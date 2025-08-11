<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\ResponseFactory;
use App\Controllers\AccountController;
use App\Services\Interfaces\AccountServiceInterface;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InvalidAmountException;
use App\Enums\HttpCodeEnum;
use App\Enums\ContentTypeEnum;

class AccountControllerTest extends TestCase
{
    private AccountServiceInterface $accountServiceMock;
    private AccountController $controller;
    private ServerRequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->accountServiceMock = $this->createMock(AccountServiceInterface::class);
        $this->controller = new AccountController($this->accountServiceMock);
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    private function createJsonRequest(array $data): Request
    {
        $body = json_encode($data);
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStream($body);

        return $this->requestFactory->createServerRequest('POST', '/event')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }

    public function testDepositSuccess()
    {
        $accountId = 123;
        $amount = 100.5;
        $newBalance = 150.5;

        $this->accountServiceMock
            ->expects($this->once())
            ->method('deposit')
            ->with($accountId, $amount)
            ->willReturn($newBalance);

        $request = $this->createJsonRequest([
            'type' => 'deposit',
            'destination' => $accountId,
            'amount' => $amount,
        ]);

        $response = $this->responseFactory->createResponse();

        $response = $this->controller->deposit($request, $response);

        $this->assertEquals(HttpCodeEnum::CREATED, $response->getStatusCode());
        $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        var_dump($json);
        $this->assertEquals($accountId, $json['destination']["id"]);
        $this->assertEquals($newBalance, $json['destination']["balance"]);
    }

    public function testDepositMissingParameters()
    {
        $request = $this->createJsonRequest([
            'type' => 'deposit',
        ]);
        $response = $this->responseFactory->createResponse();

        $response = $this->controller->deposit($request, $response);

        $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey('error', $json);
        $this->assertStringContainsString('Missing destination or amount', $json['error']);
    }

    public function testDepositInvalidParameters()
    {
        $request = $this->createJsonRequest([
            'type' => 'deposit',
            'destination' => 'invalid',
            'amount' => 'invalid',
        ]);
        $response = $this->responseFactory->createResponse();

        $response = $this->controller->deposit($request, $response);

        $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey('error', $json);
        $this->assertStringContainsString('Missing destination or amount', $json['error']);
    }

    public function testDepositAccountNotFound()
    {
        $accountId = 123;
        $amount = 100;

        $this->accountServiceMock
            ->expects($this->once())
            ->method('deposit')
            ->willThrowException(new AccountNotFoundException("Account not found"));

        $request = $this->createJsonRequest([
            'type' => 'deposit',
            'destination' => $accountId,
            'amount' => $amount,
        ]);
        $response = $this->responseFactory->createResponse();

        $response = $this->controller->deposit($request, $response);

        $this->assertEquals(HttpCodeEnum::NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey('error', $json);
        $this->assertEquals("Account not found", $json['error']);
    }

    public function testDepositInvalidAmount()
    {
        $accountId = 123;
        $amount = 0;

        $this->accountServiceMock
            ->expects($this->once())
            ->method('deposit')
            ->willThrowException(new InvalidAmountException("Invalid amount"));

        $request = $this->createJsonRequest([
            'type' => 'deposit',
            'destination' => $accountId,
            'amount' => $amount,
        ]);
        $response = $this->responseFactory->createResponse();

        $response = $this->controller->deposit($request, $response);

        $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        $json = json_decode($body, true);

        $this->assertArrayHasKey('error', $json);
        $this->assertEquals("Invalid amount", $json['error']);
    }
}
