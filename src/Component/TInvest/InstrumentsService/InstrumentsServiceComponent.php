<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService;

use Generator;
use GuzzleHttp\Client;
use Override;
use Psr\Log\LoggerInterface;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\AssetReportEventDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\BondEventDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\DividendDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\FindInstrumentRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\FindInstrumentResponseDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsResponseDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetReportsRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetBondEventsRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\InstrumentDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\TradingScheduleDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\TradingScheduleRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\FindInstrumentRequestMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\FindInstrumentResponseMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetAssetFundamentalsRequestMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetAssetFundamentalsResponseMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetAssetReportsRequestMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetAssetReportsResponseMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetBondEventsRequestMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetBondEventsResponseMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetDividendsRequestMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetDividendsResponseMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\GetInstrumentByResponseMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\TradingScheduleMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Mapper\TradingScheduleRequestMapper;
use TInvest\Core\Component\TInvest\InstrumentsService\Request\GetDividendsRequestDto;

final class InstrumentsServiceComponent implements InstrumentsServiceComponentInterface
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly GetDividendsRequestMapper $getDividendsRequestMapper,
        private readonly GetInstrumentByResponseMapper $getInstrumentByResponseMapper,
        private readonly GetDividendsResponseMapper $getDividendsResponseMapper,
        private readonly FindInstrumentRequestMapper $findInstrumentRequestMapper,
        private readonly FindInstrumentResponseMapper $findInstrumentResponseMapper,
        private readonly GetAssetFundamentalsRequestMapper $getAssetFundamentalsRequestMapper,
        private readonly GetAssetFundamentalsResponseMapper $getAssetFundamentalsResponseMapper,
        private readonly TradingScheduleRequestMapper $tradingScheduleRequestMapper,
        private readonly TradingScheduleMapper $tradingScheduleMapper,
        private readonly GetAssetReportsRequestMapper $getAssetReportsRequestMapper,
        private readonly GetAssetReportsResponseMapper $getAssetReportsResponseMapper,
        private readonly GetBondEventsRequestMapper $getBondEventsRequestMapper,
        private readonly GetBondEventsResponseMapper $getBondEventsResponseMapper,
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
    public function getInstrumentBy(string $id, string $idType, ?string $classCode = null): InstrumentDto
    {
        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/GetInstrumentBy'),
            [
                'headers' => $this->getHeaders(),
                'body' => json_encode([
                    'idType' => $idType,
                    'id' => $id,
                    'classCode' => $classCode,
                ]),
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        return $this->getInstrumentByResponseMapper->map($data);
    }

    #[Override]
    public function getDividends(GetDividendsRequestDto $dividendsRequestDto): Generator
    {
        $body = $this->getDividendsRequestMapper->map($dividendsRequestDto);
        $this->logger->debug("Get dividends: {request}", [
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/GetDividends'),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        yield from $this->getDividendsResponseMapper->map($data);
    }

    #[Override]
    public function findInstrument(FindInstrumentRequestDto $request): FindInstrumentResponseDto
    {
        $body = $this->findInstrumentRequestMapper->map($request);
        $this->logger->debug("Find instrument: {request}", [
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/FindInstrument'),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        return $this->findInstrumentResponseMapper->map($data);
    }

    #[Override]
    public function getAssetFundamentals(GetAssetFundamentalsRequestDto $request): GetAssetFundamentalsResponseDto
    {
        $body = $this->getAssetFundamentalsRequestMapper->map($request);
        $this->logger->debug("Get asset fundamentals: {request}", [
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/GetAssetFundamentals'),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        return $this->getAssetFundamentalsResponseMapper->map($data);
    }

    #[Override]
    public function getTradingSchedule(TradingScheduleRequestDto $request): TradingScheduleDto
    {
        $body = $this->tradingScheduleRequestMapper->map($request);
        $this->logger->debug("Get trading schedule: {request}", [
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/TradingSchedules'),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        return $this->tradingScheduleMapper->map($data);
    }

    #[Override]
    public function getAssetReports(GetAssetReportsRequestDto $request): Generator
    {
        $body = $this->getAssetReportsRequestMapper->map($request);
        $this->logger->debug("Get asset reports: {request}", [
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/GetAssetReports'),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        yield from $this->getAssetReportsResponseMapper->map($data);
    }

    #[Override]
    public function getBondEvents(GetBondEventsRequestDto $request): Generator
    {
        $body = $this->getBondEventsRequestMapper->map($request);
        $this->logger->debug("Get bond events: {request}", [
            'request' => $body,
        ]);

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.InstrumentsService/GetBondEvents'),
            [
                'headers' => $this->getHeaders(),
                'body' => $body,
            ]
        );

        $data = (string)$res->getBody();

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        yield from $this->getBondEventsResponseMapper->map($data);
    }
}
