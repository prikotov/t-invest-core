<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Enum;

enum CandleIntervalEnum: string
{
    case UNSPECIFIED = 'CANDLE_INTERVAL_UNSPECIFIED';
    case ONE_MIN = 'CANDLE_INTERVAL_1_MIN';
    case FIVE_MIN = 'CANDLE_INTERVAL_5_MIN';
    case FIFTEEN_MIN = 'CANDLE_INTERVAL_15_MIN';
    case HOUR = 'CANDLE_INTERVAL_HOUR';
    case DAY = 'CANDLE_INTERVAL_DAY';
    case TWO_MIN = 'CANDLE_INTERVAL_2_MIN';
    case THREE_MIN = 'CANDLE_INTERVAL_3_MIN';
    case TEN_MIN = 'CANDLE_INTERVAL_10_MIN';
    case THIRTY_MIN = 'CANDLE_INTERVAL_30_MIN';
    case TWO_HOUR = 'CANDLE_INTERVAL_2_HOUR';
    case FOUR_HOUR = 'CANDLE_INTERVAL_4_HOUR';
    case WEEK = 'CANDLE_INTERVAL_WEEK';
    case MONTH = 'CANDLE_INTERVAL_MONTH';
    case FIVE_SEC = 'CANDLE_INTERVAL_5_SEC';
    case TEN_SEC = 'CANDLE_INTERVAL_10_SEC';
    case THIRTY_SEC = 'CANDLE_INTERVAL_30_SEC';
}
