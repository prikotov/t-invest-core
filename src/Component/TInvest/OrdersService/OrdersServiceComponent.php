<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService;

use GuzzleHttp\Client;
use Override;
use Psr\Log\LoggerInterface;
use RuntimeException;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\CancelOrderResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\GetOrdersResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\OrderStateDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\PostOrderRequestDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\PostOrderResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\ReplaceOrderRequestDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\ReplaceOrderResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Mapper\GetOrdersResponseMapper;
use TInvest\Skill\Component\TInvest\OrdersService\Mapper\OrderStateResponseMapper;
use TInvest\Skill\Component\TInvest\OrdersService\Mapper\PostOrderRequestMapper;
use TInvest\Skill\Component\TInvest\OrdersService\Mapper\PostOrderResponseMapper;

final class OrdersServiceComponent implements OrdersServiceComponentInterface
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly GetOrdersResponseMapper $getOrdersResponseMapper,
        private readonly OrderStateResponseMapper $orderStateResponseMapper,
        private readonly PostOrderRequestMapper $postOrderRequestMapper,
        private readonly PostOrderResponseMapper $postOrderResponseMapper,
    ) {
    }

    private function getUrl(string $endpoint): string
    {
        return $this->baseUrl . $endpoint;
    }

    private function getHeaders(array $headers = []): array
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ], $headers);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function handleResponse(int $statusCode, array $data): void
    {
        if ($statusCode !== 200) {
            $message = $data['message'] ?? null;
            if ($message === null) {
                $encoded = json_encode($data);
                $message = $encoded !== false ? $encoded : '';
            }
            throw new RuntimeException(
                $message,
                isset($data['description']) && is_numeric($data['description'])
                    ? (int)$data['description'] : $statusCode,
            );
        }
    }

    #[Override]
    public function cancelOrder(string $accountId, string $orderId): CancelOrderResponseDto
    {
        $uri = 'tinkoff.public.invest.api.contract.v1.OrdersService/CancelOrder';
        $body = json_encode([
            'accountId' => $accountId,
            'orderId' => $orderId,
        ]);

        $this->logger->info("Cancel order: {uri} {request}", [
            'uri' => $uri,
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl($uri),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        /** @var array<string, mixed> $data */
        $data = json_decode((string)$res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->logger->info("Code: {code} {response}", [
            'code' => $res->getStatusCode(),
            'response' => json_encode($data) !== false ? json_encode($data) : '',
        ]);

        $this->handleResponse($res->getStatusCode(), $data);

        return new CancelOrderResponseDto($data['time']);
    }

    #[Override]
    public function getOrderState(string $accountId, string $orderId): OrderStateDto
    {
        $uri = 'tinkoff.public.invest.api.contract.v1.OrdersService/GetOrderState';
        $body = json_encode([
            'accountId' => $accountId,
            'orderId' => $orderId,
        ]);

        $this->logger->info("Get order state: {uri} {request}", [
            'uri' => $uri,
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl($uri),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        /** @var array<string, mixed> $data */
        $data = json_decode((string)$res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->logger->info("Code: {code} {response}", [
            'code' => $res->getStatusCode(),
            'response' => json_encode($data) !== false ? json_encode($data) : '',
        ]);

        $this->handleResponse($res->getStatusCode(), $data);

        return $this->orderStateResponseMapper->map($data);
    }

    #[Override]
    public function getOrders(string $accountId): GetOrdersResponseDto
    {
        $uri = 'tinkoff.public.invest.api.contract.v1.OrdersService/GetOrders';
        $body = json_encode([
            'accountId' => $accountId,
        ]);

        $this->logger->debug("Get orders: {uri} {request}", [
            'uri' => $uri,
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl($uri),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        /** @var array<string, mixed> $data */
        $data = json_decode((string)$res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $encoded = json_encode($data);
        $this->logger->debug($encoded !== false ? $encoded : '');

        return $this->getOrdersResponseMapper->map($data);
    }

    #[Override]
    public function postOrder(PostOrderRequestDto $postOrderRequestDto): PostOrderResponseDto
    {
        $uri = 'tinkoff.public.invest.api.contract.v1.OrdersService/PostOrder';
        $body = $this->postOrderRequestMapper->map($postOrderRequestDto);

        $this->logger->info("Post order: {uri} {request}", [
            'uri' => $uri,
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl($uri),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        /** @var array<string, mixed> $data */
        $data = json_decode((string)$res->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->logger->info("Code: {code} {response}", [
            'code' => $res->getStatusCode(),
            'response' => json_encode($data) !== false ? json_encode($data) : '',
        ]);

        $this->handleResponse($res->getStatusCode(), $data);

        return $this->postOrderResponseMapper->map($data);
    }

    #[Override]
    public function replaceOrder(ReplaceOrderRequestDto $replaceOrderRequestDto): ReplaceOrderResponseDto
    {
        return new ReplaceOrderResponseDto();
    }
}
