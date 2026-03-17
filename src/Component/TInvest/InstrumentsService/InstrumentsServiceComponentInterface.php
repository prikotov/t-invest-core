<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService;

use Generator;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\DividendDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\InstrumentDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\Request\GetDividendsRequestDto;

interface InstrumentsServiceComponentInterface
{
    public function getInstrumentBy(string $id, int $idType, ?string $classCode = null): InstrumentDto;

    public function getDividends(GetDividendsRequestDto $dividendsRequestDto): Generator;
}
