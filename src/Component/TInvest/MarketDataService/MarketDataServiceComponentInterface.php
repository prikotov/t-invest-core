<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService;

use TInvest\Skill\Component\TInvest\MarketDataService\Dto\GetCandlesRequestDto;
use TInvest\Skill\Component\TInvest\MarketDataService\Dto\GetCandlesResponseDto;
use TInvest\Skill\Component\TInvest\MarketDataService\Dto\GetLastPricesRequestDto;
use TInvest\Skill\Component\TInvest\MarketDataService\Dto\GetLastPricesResponseDto;

interface MarketDataServiceComponentInterface
{
    public function getCandles(GetCandlesRequestDto $request): GetCandlesResponseDto;

    public function getLastPrices(GetLastPricesRequestDto $request): GetLastPricesResponseDto;
}
