<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\UsersService;

use Generator;
use GuzzleHttp\Client;
use Override;
use TInvest\Skill\Component\TInvest\UsersService\Dto\AccountDto;
use TInvest\Skill\Component\TInvest\UsersService\Mapper\GetAccountsResponseMapper;

final class UsersServiceComponent implements UsersServiceComponentInterface
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly Client $client,
        private readonly GetAccountsResponseMapper $getAccountsResponseMapper,
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
    public function getAccounts(): Generator
    {
        $res = $this->client->post(
            $this->getUrl('tinkoff.public.invest.api.contract.v1.UsersService/GetAccounts'),
            [
                'headers' => $this->getHeaders(),
                'body' => '{}',
            ]
        );

        yield from $this->getAccountsResponseMapper->map((string)$res->getBody());
    }
}
