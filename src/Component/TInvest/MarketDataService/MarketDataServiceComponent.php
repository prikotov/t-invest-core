<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService;

use GuzzleHttp\Client;
use Override;
use Psr\Log\LoggerInterface;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetOrderBookRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetOrderBookResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\CandleMapper;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\LastPriceMapper;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\OrderBookMapper;

final class MarketDataServiceComponent implements MarketDataServiceComponentInterface
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly CandleMapper $candleMapper,
        private readonly LastPriceMapper $lastPriceMapper,
        private readonly OrderBookMapper $orderBookMapper,
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

    #[Override]
    public function getCandles(GetCandlesRequestDto $request): GetCandlesResponseDto
    {
        $body = [
            'instrumentId' => $request->instrumentId,
            'from' => $request->from->format('c'),
            'to' => $request->to->format('c'),
            'interval' => $request->interval->value,
        ];

        if ($request->limit !== null) {
            $body['limit'] = $request->limit;
        }

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.MarketDataService/GetCandles'),
            [
                'headers' => $this->getHeaders(),
                'body' => json_encode($body),
            ]
        );

        $data = (string)$res->getBody();
        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        if (empty($data)) {
            return $this->candleMapper->map([]);
        }

        /** @var array<string, mixed> */
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        return $this->candleMapper->map($decoded);
    }

    #[Override]
    public function getLastPrices(GetLastPricesRequestDto $request): GetLastPricesResponseDto
    {
        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.MarketDataService/GetLastPrices'),
            [
                'headers' => $this->getHeaders(),
                'body' => json_encode([
                    'instrumentId' => $request->instrumentIds,
                ]),
            ]
        );

        $data = (string)$res->getBody();
        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        if (empty($data)) {
            return $this->lastPriceMapper->map([]);
        }

        /** @var array<string, mixed> */
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        return $this->lastPriceMapper->map($decoded);
    }

    #[Override]
    public function getOrderBook(GetOrderBookRequestDto $request): GetOrderBookResponseDto
    {
        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.MarketDataService/GetOrderBook'),
            [
                'headers' => $this->getHeaders(),
                'body' => json_encode([
                    'instrumentId' => $request->instrumentId,
                    'depth' => $request->depth,
                ]),
            ]
        );

        $data = (string)$res->getBody();
        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        if (empty($data)) {
            return $this->orderBookMapper->map([]);
        }

        /** @var array<string, mixed> */
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        return $this->orderBookMapper->map($decoded);
    }
}
