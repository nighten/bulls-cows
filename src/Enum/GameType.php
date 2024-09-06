<?php

declare(strict_types=1);

namespace Nighten\Bc\Enum;

enum GameType: int
{
    case User = 1;
    case Comp = 2;
    case Together = 3;
}
