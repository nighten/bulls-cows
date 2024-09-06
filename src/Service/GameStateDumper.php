<?php

declare(strict_types=1);

namespace Nighten\Bc\Service;

use Nighten\Bc\Game;
use Nighten\Bc\State\GameState;
use Throwable;

class GameStateDumper
{
    private const string FILE_NAME = 'state.dump';

    public function dump(Game $game): void
    {
        $state = $game->getState();
        file_put_contents(self::FILE_NAME, serialize($state));
    }

    public function load(Game $game): bool
    {
        if (file_exists(self::FILE_NAME)) {
            $content = file_get_contents(self::FILE_NAME);
            if (is_string($content)) {
                $state = unserialize($content);
                if ($state instanceof GameState) {
                    $game->loadState($state);
                    return true;
                }
            }
        }
        return false;
    }

    public function reset(): void
    {
        if (file_exists(self::FILE_NAME)) {
            unlink(self::FILE_NAME);
        }
    }
}
