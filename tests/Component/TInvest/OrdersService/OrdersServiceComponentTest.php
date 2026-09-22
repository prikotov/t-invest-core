<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OrdersService;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use TInvest\Core\Component\TInvest\OrdersService\Mapper\GetOrdersResponseMapper;
use TInvest\Core\Component\TInvest\OrdersService\Mapper\OrderStateResponseMapper;
use TInvest\Core\Component\TInvest\OrdersService\Mapper\PostOrderRequestMapper;
use TInvest\Core\Component\TInvest\OrdersService\Mapper\PostOrderResponseMapper;
use TInvest\Core\Component\TInvest\OrdersService\OrdersServiceComponent;
use TInvest\Core\Component\TInvest\Shared\Exception\TInvestRequestException;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class OrdersServiceComponentTest extends TestCase
{
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
    }

    private function createComponent(bool $httpErrors = true): OrdersServiceComponent
    {
        $client = new Client([
            'handler' => HandlerStack::create($this->mockHandler),
            'http_errors' => $httpErrors,
        ]);

        return new OrdersServiceComponent(
            'test-token',
            'https://invest-public-api.test.local/',
            $client,
            new NullLogger(),
            new GetOrdersResponseMapper(new OrderStateResponseMapper(new MoneyFactory())),
            new OrderStateResponseMapper(new MoneyFactory()),
            new PostOrderRequestMapper(),
            new PostOrderResponseMapper(new MoneyFactory(), new QuotationFactory()),
        );
    }

    private function queueResponse(int $statusCode, string $body): void
    {
        $this->mockHandler->append(
            new Response($statusCode, ['Content-Type' => 'application/json'], $body),
        );
    }

    private function orderStateBody(): string
    {
        return (string) json_encode([
            'orderId' => 'order-123',
            'executionReportStatus' => 'EXECUTION_REPORT_STATUS_FILL',
            'lotsRequested' => '10',
            'lotsExecuted' => '10',
            'initialOrderPrice' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'executedOrderPrice' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'totalOrderAmount' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'averagePositionPrice' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
            'initialCommission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
            'executedCommission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
            'figi' => 'BBG000000001',
            'direction' => 'ORDER_DIRECTION_BUY',
            'initialSecurityPrice' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
            'stages' => [],
            'serviceCommission' => ['currency' => 'RUB', 'units' => '0', 'nano' => 50000000],
            'currency' => 'RUB',
            'orderType' => 'ORDER_TYPE_LIMIT',
            'orderDate' => '2024-01-15T10:30:00Z',
            'instrumentUid' => 'inst-1',
            'orderRequestId' => 'req-1',
        ]);
    }

    public function testGetOrderStateMapsSuccessfulResponse(): void
    {
        $this->queueResponse(200, $this->orderStateBody());
        $component = $this->createComponent();

        $state = $component->getOrderState('test-account', 'order-123');

        $this->assertSame('order-123', $state->orderId);
        $this->assertSame('EXECUTION_REPORT_STATUS_FILL', $state->executionReportStatus);
        $this->assertSame(10, $state->lotsExecuted);
        $this->assertSame('BBG000000001', $state->figi);
    }

    public function testGetOrderStateGuzzleHttpErrorThrowsRequestException(): void
    {
        $this->queueResponse(
            400,
            (string) json_encode(['message' => 'Order not found.', 'description' => '400']),
        );
        $component = $this->createComponent();

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('400 Bad Request');

        $component->getOrderState('test-account', 'order-unknown');
    }

    public function testGetOrderStateHttpErrorThrowsTInvestRequestExceptionWithBrokerMessage(): void
    {
        $this->queueResponse(
            400,
            (string) json_encode(['message' => 'Order not found.', 'description' => '400']),
        );
        $component = $this->createComponent(httpErrors: false);

        try {
            $component->getOrderState('test-account', 'order-unknown');
            $this->fail('Expected TInvestRequestException to be thrown.');
        } catch (TInvestRequestException $exception) {
            $this->assertSame('Order not found.', $exception->getMessage());
            $this->assertSame(400, $exception->getCode());
            $this->assertInstanceOf(RuntimeException::class, $exception);
        }
    }

    public function testGetOrderStateHttpErrorWithoutMessageThrowsEncodedBody(): void
    {
        $body = (string) json_encode(['error' => 'broken']);
        $this->queueResponse(500, $body);
        $component = $this->createComponent(httpErrors: false);

        try {
            $component->getOrderState('test-account', 'order-123');
            $this->fail('Expected TInvestRequestException to be thrown.');
        } catch (TInvestRequestException $exception) {
            $this->assertSame($body, $exception->getMessage());
            $this->assertSame(500, $exception->getCode());
        }
    }
}
