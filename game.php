<?php

use Nighten\Bc\Cli\GameCommand;
use Nighten\Bc\Service\GameStateDumper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application('Bulls and Cows game');
$output = new ConsoleOutput();

$gameCommand = new GameCommand(
    new GameStateDumper(),
);
$app->add($gameCommand);

$app->run(null, $output);


