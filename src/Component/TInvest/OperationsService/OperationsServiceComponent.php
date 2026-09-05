<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService;

use GuzzleHttp\Client;
use Override;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsRequestDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PortfolioDto;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetPortfolioResponseMapper;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\OperationMapper;
use UnexpectedValueException;

final class OperationsServiceComponent implements OperationsServiceComponentInterface
{
    public function __construct(
        private readonly string $token,
        private readonly string $accountId,
        private readonly string $baseUrl,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly GetPortfolioResponseMapper $getPortfolioResponseMapper,
        private readonly OperationMapper $operationMapper,
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
    public function getPortfolio(): PortfolioDto
    {
        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.OperationsService/GetPortfolio'),
            [
                'headers' => $this->getHeaders(),
                'body' => json_encode([
                    'accountId' => $this->accountId,
                ]),
            ]
        );

        $data = (string)$res->getBody();

        if ($data === '') {
            throw new RuntimeException('GetPortfolio: empty response body.');
        }

        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        $decoded = json_decode($data, false, 512, JSON_THROW_ON_ERROR);

        if (!$decoded instanceof stdClass) {
            throw new UnexpectedValueException(sprintf(
                'GetPortfolio: response body must be a JSON object, %s given.',
                get_debug_type($decoded),
            ));
        }

        if (!property_exists($decoded, 'positions')) {
            throw new UnexpectedValueException('GetPortfolio: field "positions" is missing.');
        }

        if (!is_array($decoded->positions)) {
            throw new UnexpectedValueException(sprintf(
                'GetPortfolio: field "positions" must be a JSON array, %s given.',
                get_debug_type($decoded->positions),
            ));
        }

        /** @var array<string, mixed> $assoc */
        $assoc = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        return $this->getPortfolioResponseMapper->map($assoc);
    }

    #[Override]
    public function getOperations(GetOperationsRequestDto $request): GetOperationsResponseDto
    {
        $body = [
            'accountId' => $this->accountId,
            'from' => $request->from->format('c'),
            'to' => $request->to->format('c'),
        ];

        if ($request->state !== null) {
            $body['state'] = $request->state->value;
        }

        if ($request->figi !== null) {
            $body['figi'] = $request->figi;
        }

        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.OperationsService/GetOperations'),
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
            return $this->operationMapper->map([]);
        }

        /** @var array<string, mixed> */
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        return $this->operationMapper->map($decoded);
    }
}
