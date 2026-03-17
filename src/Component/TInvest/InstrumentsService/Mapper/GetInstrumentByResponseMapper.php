<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Mapper;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\InstrumentDto;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuantityFactory;

final class GetInstrumentByResponseMapper
{
    public function __construct(
        private readonly QuantityFactory $quantityFactory,
    ) {
    }

    public function map(string $json): InstrumentDto
    {
        /** @var array{instrument: array<string, mixed>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $instrument = $data['instrument'];

        /** @var array<string, mixed> $klong */
        $klong = $instrument['klong'];
        /** @var array<string, mixed> $kshort */
        $kshort = $instrument['kshort'];
        /** @var array<string, mixed> $dlong */
        $dlong = $instrument['dlong'];
        /** @var array<string, mixed> $dshort */
        $dshort = $instrument['dshort'];
        /** @var array<string, mixed> $dlongMin */
        $dlongMin = $instrument['dlongMin'];
        /** @var array<string, mixed> $dshortMin */
        $dshortMin = $instrument['dshortMin'];
        /** @var array<string, mixed> $minPriceIncrement */
        $minPriceIncrement = $instrument['minPriceIncrement'];
        /** @var string|null $first1minCandleDate */
        $first1minCandleDate = $instrument['first1minCandleDate'] ?? null;
        /** @var string|null $first1dayCandleDate */
        $first1dayCandleDate = $instrument['first1dayCandleDate'] ?? null;

        return new InstrumentDto(
            figi: (string)$instrument['figi'],
            ticker: (string)$instrument['ticker'],
            classCode: (string)$instrument['classCode'],
            isin: isset($instrument['isin']) ? (string)$instrument['isin'] : null,
            lot: (int)$instrument['lot'],
            currency: (string)$instrument['currency'],
            klong: $this->quantityFactory->create($klong),
            kshort: $this->quantityFactory->create($kshort),
            dlong: $this->quantityFactory->create($dlong),
            dshort: $this->quantityFactory->create($dshort),
            dlongMin: $this->quantityFactory->create($dlongMin),
            dshortMin: $this->quantityFactory->create($dshortMin),
            shortEnabledFlag: (bool)$instrument['shortEnabledFlag'],
            name: (string)$instrument['name'],
            exchange: (string)$instrument['exchange'],
            countryOfRisk: isset($instrument['countryOfRisk']) ? (string)$instrument['countryOfRisk'] : null,
            countryOfRiskName: isset($instrument['countryOfRiskName']) ? (string)$instrument['countryOfRiskName'] : null,
            instrumentType: (string)$instrument['instrumentType'],
            tradingStatus: (int)$instrument['tradingStatus'],
            otcFlag: isset($instrument['otcFlag']) ? (bool)$instrument['otcFlag'] : null,
            buyAvailableFlag: (bool)$instrument['buyAvailableFlag'],
            sellAvailableFlag: (bool)$instrument['sellAvailableFlag'],
            minPriceIncrement: $this->quantityFactory->create($minPriceIncrement),
            apiTradeAvailableFlag: isset($instrument['apiTradeAvailableFlag']) ? (bool)$instrument['apiTradeAvailableFlag'] : null,
            uid: (string)$instrument['uid'],
            realExchange: (string)$instrument['realExchange'],
            positionUid: (string)$instrument['positionUid'],
            forIisFlag: (bool)$instrument['forIisFlag'],
            forQualInvestorFlag: (bool)$instrument['forQualInvestorFlag'],
            weekendFlag: (bool)$instrument['weekendFlag'],
            blockedTcaFlag: (bool)$instrument['blockedTcaFlag'],
            instrumentKind: (string)$instrument['instrumentKind'],
            first1minCandleDate: $first1minCandleDate !== null ? new DateTimeImmutable($first1minCandleDate) : null,
            first1dayCandleDate: $first1dayCandleDate !== null ? new DateTimeImmutable($first1dayCandleDate) : null,
        );
    }
}
