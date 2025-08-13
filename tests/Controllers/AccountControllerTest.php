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
    use App\Services\Interfaces\ResetMemoryServiceInterface;

    class AccountControllerTest extends TestCase
    {
        private AccountServiceInterface $accountServiceMock;
        private ResetMemoryServiceInterface $memoryServiceMock;
        private AccountController $controller;
        private ServerRequestFactory $requestFactory;
        private ResponseFactory $responseFactory;

        protected function setUp(): void
        {
            $this->accountServiceMock = $this->createMock(AccountServiceInterface::class);
            $this->memoryServiceMock = $this->createMock(ResetMemoryServiceInterface::class);
            $this->controller = new AccountController($this->accountServiceMock, $this->memoryServiceMock);
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

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::CREATED, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertEquals($accountId, $json['destination']["id"]);
            $this->assertEquals($newBalance, $json['destination']["balance"]);
        }

        public function testDepositMissingParameters()
        {
            $request = $this->createJsonRequest([
                'type' => 'deposit',
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertStringContainsString('Missing destination account ID.', $json['error']);
        }

        public function testDepositInvalidParameters()
        {
            $request = $this->createJsonRequest([
                'type' => 'deposit',
                'destination' => 'invalid',
                'amount' => 'invalid',
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertStringContainsString('Missing destination account ID.', $json['error']);
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

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::NOT_FOUND, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));
        }

        public function testDepositInvalidAmount()
        {
            $accountId = 123;
            $amount = 0;

            $request = $this->createJsonRequest([
                'type' => 'deposit',
                'destination' => $accountId,
                'amount' => $amount,
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertEquals("Missing amount.", $json['error']);
        }

        public function testResetSuccess()
        {
            $memoryServiceMock = $this->createMock(ResetMemoryServiceInterface::class);
            $memoryServiceMock->expects($this->once())
                ->method('resetMemory')
                ->willReturn(true);

            $controller = new \App\Controllers\AccountController(
                $this->accountServiceMock,
                $memoryServiceMock
            );

            $request = $this->requestFactory->createServerRequest('POST', '/reset');
            $response = $this->responseFactory->createResponse();

            $response = $controller->reset($request, $response);

            $this->assertEquals(HttpCodeEnum::OK, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));
            $this->assertEquals('OK', (string)$response->getBody());
        }

        public function testResetFailure()
        {
            $memoryServiceMock = $this->createMock(ResetMemoryServiceInterface::class);
            $memoryServiceMock->expects($this->once())
                ->method('resetMemory')
                ->willReturn(false);

            $controller = new AccountController(
                $this->accountServiceMock,
                $memoryServiceMock
            );

            $request = $this->requestFactory->createServerRequest('POST', '/reset');
            $response = $this->responseFactory->createResponse();

            $response = $controller->reset($request, $response);

            $this->assertEquals(HttpCodeEnum::INTERNAL_SERVER_ERROR, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));
            $this->assertEquals('OK', (string)$response->getBody());
        }

        public function testWithdrawSuccess()
        {
            $accountId = 123;
            $amount = 50.0;
            $newBalance = 50.0;

            $this->accountServiceMock
                ->expects($this->once())
                ->method('withdraw')
                ->with($accountId, $amount)
                ->willReturn($newBalance);

            $request = $this->createJsonRequest([
                'type' => 'withdraw',
                'origin' => $accountId,
                'amount' => $amount,
            ]);

            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::CREATED, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertEquals($accountId, $json['origin']["id"]);
            $this->assertEquals($newBalance, $json['origin']["balance"]);
        }

        public function testWithdrawMissingParameters()
        {
            $request = $this->createJsonRequest([
                'type' => 'withdraw',
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertStringContainsString('Missing origin account ID.', $json['error']);
        }

        public function testWithdrawInvalidParameters()
        {
            $request = $this->createJsonRequest([
                'type' => 'withdraw',
                'origin' => 'invalid',
                'amount' => 'invalid',
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertStringContainsString('Missing origin account ID.', $json['error']);
        }

        public function testWithdrawAccountNotFound()
        {
            $accountId = 123;
            $amount = 50;

            $this->accountServiceMock
                ->expects($this->once())
                ->method('withdraw')
                ->willThrowException(new AccountNotFoundException("Account not found"));

            $request = $this->createJsonRequest([
                'type' => 'withdraw',
                'origin' => $accountId,
                'amount' => $amount,
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::NOT_FOUND, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));
        }

        public function testWithdrawInvalidAmount()
        {
            $accountId = 123;
            $amount = 0;

            $request = $this->createJsonRequest([
                'type' => 'withdraw',
                'origin' => $accountId,
                'amount' => $amount,
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertEquals("Missing amount.", $json['error']);
        }

        public function testTransferWithSuccess()
        {
            $originId = 123;
            $destinationId = 456;
            $amount = 75.0;
            $newOriginBalance = 25;
            $newDestinationBalance = 175.0;

            $this->accountServiceMock
                ->expects($this->once())
                ->method('transfer')
                ->with($originId, $destinationId, $amount)
                ->willReturn([
                    'origin' => $newOriginBalance,
                    'destination' => $newDestinationBalance,
                ]);

            $request = $this->createJsonRequest([
                'type' => 'transfer',
                'origin' => $originId,
                'destination' => $destinationId,
                'amount' => $amount,
            ]);

            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::CREATED, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertEquals($originId, $json['origin']["id"]);
            $this->assertEquals($newOriginBalance, $json['origin']["balance"]);
            $this->assertEquals($destinationId, $json['destination']["id"]);
            $this->assertEquals($newDestinationBalance, $json['destination']["balance"]);
        }

        public function testTransferMissingParameters()
        {
            $request = $this->createJsonRequest([
                'type' => 'transfer',
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertStringContainsString('Missing origin account ID.', $json['error']);
        }

        public function testTransferInvalidParameters()
        {
            $request = $this->createJsonRequest([
                'type' => 'transfer',
                'origin' => 'invalid',
                'destination' => 'invalid',
                'amount' => 'invalid',
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertStringContainsString('Missing origin account ID.', $json['error']);
        }

        public function testTransferAccountNotFound()
        {
            $originId = 123;
            $destinationId = 456;
            $amount = 75.0;

            $this->accountServiceMock
                ->expects($this->once())
                ->method('transfer')
                ->willThrowException(new AccountNotFoundException("Account not found"));

            $request = $this->createJsonRequest([
                'type' => 'transfer',
                'origin' => $originId,
                'destination' => $destinationId,
                'amount' => $amount,
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::NOT_FOUND, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));
        }

        public function testTransferInvalidAmount()
        {
            $originId = 123;
            $destinationId = 456;
            $amount = 0;

            $request = $this->createJsonRequest([
                'type' => 'transfer',
                'origin' => $originId,
                'destination' => $destinationId,
                'amount' => $amount,
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::BAD_REQUEST, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertEquals("Missing amount.", $json['error']);
        }

        public function testEventTypeNotFound()
        {
            $type = 'unknown_event';
            $request = $this->createJsonRequest([
                'type' => $type,
            ]);
            $response = $this->responseFactory->createResponse();

            $response = $this->controller->event($request, $response);

            $this->assertEquals(HttpCodeEnum::NOT_FOUND, $response->getStatusCode());
            $this->assertEquals(ContentTypeEnum::JSON, $response->getHeaderLine('Content-Type'));

            $body = (string)$response->getBody();
            $json = json_decode($body, true);

            $this->assertArrayHasKey('error', $json);
            $this->assertEquals("Unsupported event type: {$type}", $json['error']);
        }
    }
