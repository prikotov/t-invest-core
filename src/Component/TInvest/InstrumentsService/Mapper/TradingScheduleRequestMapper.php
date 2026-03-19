<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\TradingScheduleRequestDto;

final class TradingScheduleRequestMapper
{
    public function map(TradingScheduleRequestDto $request): string
    {
        $body = [
            'exchange' => $request->exchange,
        ];

        if ($request->from !== null) {
            $body['from'] = $request->from;
        }
        if ($request->to !== null) {
            $body['to'] = $request->to;
        }

        return (string)json_encode($body);
    }
}
