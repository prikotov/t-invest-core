<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OperationsService;

use GuzzleHttp\Client;
use Override;
use Psr\Log\LoggerInterface;
use TInvest\Skill\Component\TInvest\OperationsService\Dto\PortfolioDto;
use TInvest\Skill\Component\TInvest\OperationsService\Mapper\GetPortfolioResponseMapper;

final class OperationsServiceComponent implements OperationsServiceComponentInterface
{
    public function __construct(
        private readonly string $token,
        private readonly string $accountId,
        private readonly string $baseUrl,
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly GetPortfolioResponseMapper $getPortfolioResponseMapper,
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
        $encoded = json_encode(json_decode($data));
        if ($encoded !== false) {
            $this->logger->debug($encoded);
        }

        if (empty($data)) {
            return $this->getPortfolioResponseMapper->map([]);
        }

        /** @var array<string, mixed> */
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        return $this->getPortfolioResponseMapper->map($decoded);
    }
}
