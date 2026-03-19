<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService;

use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesResponseDto;

interface MarketDataServiceComponentInterface
{
    public function getCandles(GetCandlesRequestDto $request): GetCandlesResponseDto;

    public function getLastPrices(GetLastPricesRequestDto $request): GetLastPricesResponseDto;
}
