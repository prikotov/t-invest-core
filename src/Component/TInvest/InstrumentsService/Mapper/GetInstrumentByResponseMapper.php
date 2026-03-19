<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\InstrumentDto;
use TInvest\Core\Component\TInvest\Shared\Factory\QuantityFactory;

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

        $emptyQuantity = ['units' => '0', 'nano' => 0];

        return new InstrumentDto(
            figi: (string)$instrument['figi'],
            ticker: (string)$instrument['ticker'],
            classCode: (string)$instrument['classCode'],
            isin: isset($instrument['isin']) ? (string)$instrument['isin'] : null,
            lot: (int)$instrument['lot'],
            currency: (string)$instrument['currency'],
            klong: $this->quantityFactory->create($instrument['klong'] ?? $emptyQuantity),
            kshort: $this->quantityFactory->create($instrument['kshort'] ?? $emptyQuantity),
            dlong: $this->quantityFactory->create($instrument['dlong'] ?? $emptyQuantity),
            dshort: $this->quantityFactory->create($instrument['dshort'] ?? $emptyQuantity),
            dlongMin: $this->quantityFactory->create($instrument['dlongMin'] ?? $emptyQuantity),
            dshortMin: $this->quantityFactory->create($instrument['dshortMin'] ?? $emptyQuantity),
            shortEnabledFlag: (bool)($instrument['shortEnabledFlag'] ?? false),
            name: (string)$instrument['name'],
            exchange: (string)$instrument['exchange'],
            countryOfRisk: isset($instrument['countryOfRisk']) ? (string)$instrument['countryOfRisk'] : null,
            countryOfRiskName: isset($instrument['countryOfRiskName']) ? (string)$instrument['countryOfRiskName'] : null,
            instrumentType: (string)$instrument['instrumentType'],
            tradingStatus: (int)$instrument['tradingStatus'],
            otcFlag: isset($instrument['otcFlag']) ? (bool)$instrument['otcFlag'] : null,
            buyAvailableFlag: (bool)($instrument['buyAvailableFlag'] ?? false),
            sellAvailableFlag: (bool)($instrument['sellAvailableFlag'] ?? false),
            minPriceIncrement: $this->quantityFactory->create($instrument['minPriceIncrement'] ?? $emptyQuantity),
            apiTradeAvailableFlag: isset($instrument['apiTradeAvailableFlag']) ? (bool)$instrument['apiTradeAvailableFlag'] : null,
            uid: (string)$instrument['uid'],
            realExchange: (string)$instrument['realExchange'],
            positionUid: (string)$instrument['positionUid'],
            forIisFlag: (bool)($instrument['forIisFlag'] ?? false),
            forQualInvestorFlag: (bool)($instrument['forQualInvestorFlag'] ?? false),
            weekendFlag: (bool)($instrument['weekendFlag'] ?? false),
            blockedTcaFlag: (bool)($instrument['blockedTcaFlag'] ?? false),
            instrumentKind: (string)$instrument['instrumentKind'],
            first1minCandleDate: isset($instrument['first1minCandleDate']) ? new DateTimeImmutable($instrument['first1minCandleDate']) : null,
            first1dayCandleDate: isset($instrument['first1dayCandleDate']) ? new DateTimeImmutable($instrument['first1dayCandleDate']) : null,
            assetUid: (string)($instrument['assetUid'] ?? ''),
        );
    }
}
