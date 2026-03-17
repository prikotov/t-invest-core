<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Enum;

enum OrderExecutionReportStatusEnum: int
{
    case EXECUTION_REPORT_STATUS_UNSPECIFIED = 0;
    case EXECUTION_REPORT_STATUS_FILL = 1;
    case EXECUTION_REPORT_STATUS_REJECTED = 2;
    case EXECUTION_REPORT_STATUS_CANCELLED = 3;
    case EXECUTION_REPORT_STATUS_NEW = 4;
    case EXECUTION_REPORT_STATUS_PARTIALLYFILL = 5;
}
