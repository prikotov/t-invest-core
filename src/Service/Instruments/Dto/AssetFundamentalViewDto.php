<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Instruments\Dto;

final readonly class AssetFundamentalViewDto
{
    public function __construct(
        public string $ticker,
        public ?float $marketCapitalization,
        public ?float $peRatioTtm,
        public ?float $priceToBookTtm,
        public ?float $priceToSalesTtm,
        public ?float $roe,
        public ?float $roa,
        public ?float $dividendYieldDailyTtm,
        public ?float $epsTtm,
        public ?float $revenueTtm,
        public ?float $netIncomeTtm,
        public ?float $ebitdaTtm,
        public ?float $freeCashFlowTtm,
        public ?float $beta,
        public ?float $highPriceLast52Weeks,
        public ?float $lowPriceLast52Weeks,
        public ?string $currency,
    ) {
    }
}
