<?php

declare(strict_types=1);

namespace Phpolar\Migrations;

enum LedgerInsertStatus
{
    case FAILED;
    case SUCCESSFUL;
}
