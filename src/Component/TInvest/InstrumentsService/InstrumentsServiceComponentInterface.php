<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService;

use Generator;
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
use TInvest\Core\Component\TInvest\InstrumentsService\Request\GetDividendsRequestDto;

interface InstrumentsServiceComponentInterface
{
    public function getInstrumentBy(string $id, string $idType, ?string $classCode = null): InstrumentDto;

    public function getDividends(GetDividendsRequestDto $dividendsRequestDto): Generator;

    public function getAssetReports(GetAssetReportsRequestDto $request): Generator;

    public function getBondEvents(GetBondEventsRequestDto $request): Generator;

    public function findInstrument(FindInstrumentRequestDto $request): FindInstrumentResponseDto;

    public function getAssetFundamentals(GetAssetFundamentalsRequestDto $request): GetAssetFundamentalsResponseDto;

    public function getTradingSchedule(TradingScheduleRequestDto $request): TradingScheduleDto;
}
