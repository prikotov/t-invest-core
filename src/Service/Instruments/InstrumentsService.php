<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Instruments;

use Override;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\FindInstrumentRequestDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsRequestDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\InstrumentsServiceComponentInterface;
use TInvest\Skill\Service\Instruments\Dto\AssetFundamentalViewDto;

final class InstrumentsService implements InstrumentsServiceInterface
{
    public function __construct(
        private readonly InstrumentsServiceComponentInterface $component,
    ) {
    }

    #[Override]
    public function getAssetUidByTicker(string $ticker): ?string
    {
        $findResponse = $this->component->findInstrument(new FindInstrumentRequestDto($ticker));

        $uid = null;
        foreach ($findResponse->instruments as $instrument) {
            if ($instrument->ticker === $ticker) {
                $uid = $instrument->uid;
                break;
            }
        }

        if ($uid === null && $findResponse->instruments !== []) {
            $uid = $findResponse->instruments[0]->uid;
        }

        if ($uid === null) {
            return null;
        }

        $instrument = $this->component->getInstrumentBy($uid, 'INSTRUMENT_ID_TYPE_UID');
        return $instrument->assetUid;
    }

    #[Override]
    public function getTickerByAssetUid(string $assetUid): ?string
    {
        $instrument = $this->component->getInstrumentBy($assetUid, 'INSTRUMENT_ID_TYPE_UID');
        return $instrument->ticker;
    }

    #[Override]
    public function getFundamentalsByTickers(array $tickers): array
    {
        $assetUids = [];
        $tickerToUid = [];

        foreach ($tickers as $ticker) {
            $uid = $this->getAssetUidByTicker($ticker);
            if ($uid !== null) {
                $assetUids[] = $uid;
                $tickerToUid[$uid] = $ticker;
            }
        }

        if ($assetUids === []) {
            return [];
        }

        $response = $this->component->getAssetFundamentals(new GetAssetFundamentalsRequestDto($assetUids));

        $result = [];
        foreach ($response->fundamentals as $fundamental) {
            $ticker = $tickerToUid[$fundamental->assetUid] ?? $fundamental->assetUid;
            $result[] = new AssetFundamentalViewDto(
                ticker: $ticker,
                marketCapitalization: $fundamental->marketCapitalization,
                peRatioTtm: $fundamental->peRatioTtm,
                priceToBookTtm: $fundamental->priceToBookTtm,
                priceToSalesTtm: $fundamental->priceToSalesTtm,
                roe: $fundamental->roe,
                roa: $fundamental->roa,
                dividendYieldDailyTtm: $fundamental->dividendYieldDailyTtm,
                epsTtm: $fundamental->epsTtm,
                revenueTtm: $fundamental->revenueTtm,
                netIncomeTtm: $fundamental->netIncomeTtm,
                ebitdaTtm: $fundamental->ebitdaTtm,
                freeCashFlowTtm: $fundamental->freeCashFlowTtm,
                beta: $fundamental->beta,
                highPriceLast52Weeks: $fundamental->highPriceLast52Weeks,
                lowPriceLast52Weeks: $fundamental->lowPriceLast52Weeks,
                currency: $fundamental->currency,
            );
        }

        return $result;
    }
}
