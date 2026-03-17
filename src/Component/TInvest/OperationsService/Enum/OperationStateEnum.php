<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OperationsService\Enum;

enum OperationStateEnum: string
{
    case UNSPECIFIED = 'OPERATION_STATE_UNSPECIFIED';
    case EXECUTED = 'OPERATION_STATE_EXECUTED';
    case CANCELED = 'OPERATION_STATE_CANCELED';
    case PROGRESS = 'OPERATION_STATE_PROGRESS';
}
