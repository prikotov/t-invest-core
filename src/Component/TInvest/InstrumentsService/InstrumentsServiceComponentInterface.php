<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService;

use Generator;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\DividendDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\FindInstrumentRequestDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\FindInstrumentResponseDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsRequestDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsResponseDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\InstrumentDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Request\GetDividendsRequestDto;

interface InstrumentsServiceComponentInterface
{
    public function getInstrumentBy(string $id, string $idType, ?string $classCode = null): InstrumentDto;

    public function getDividends(GetDividendsRequestDto $dividendsRequestDto): Generator;

    public function findInstrument(FindInstrumentRequestDto $request): FindInstrumentResponseDto;

    public function getAssetFundamentals(GetAssetFundamentalsRequestDto $request): GetAssetFundamentalsResponseDto;
}
