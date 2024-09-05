<?php

declare(strict_types=1);

namespace Nighten\Bc\Cli;

use Nighten\Bc\Exception\GameException;
use Nighten\Bc\Exception\GameIsRunningException;
use Nighten\Bc\Game;
use Nighten\Bc\GameFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[
    AsCommand(
        'game:start',
    )
]
class GameCommand extends Command
{
    /**
     * @throws GameIsRunningException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting game Bulls and Cows</info>');
        $output->writeln('<info>For end game type "end"</info>');
        $output->writeln('<info>For start new game type "new"</info>');

        $helper = $this->getHelper('question');
        $question = new Question('Enter number: ');
        $requestSection = $output->section();
        $resultSection = $output->section();
        $table = $this->initTable($resultSection);

        $game = GameFactory::create();
        if ($this->loadState($game)) {
            foreach ($game->getState()->getTurns() as $turn) {
                $table->addRow([$turn->number->asString(), $turn->bulls, $turn->cows]);
            }
        } else {
            $game->start();
        }

        $table->render();

        $win = false;
        while (true) {
            $number = $helper->ask($input, $requestSection, $question);
            if ('end' === $number) {
                break;
            }
            if ('new' === $number) {
                $this->resetState();
                $game->restart();
                $requestSection = $output->section();
                $requestSection->writeln('<info>Game was restarted</info>');
                $resultSection = $output->section();
                $table = $this->initTable($resultSection);
                continue;
            }
            if (null !== $number) {
                try {
                    $turn = $game->turn($number);
                    $requestSection->writeln('Turn: ' . $number . ' Bulls: ' . $turn->bulls . ' Cows: ' . $turn->cows);
                    $table->appendRow([$number, $turn->bulls, $turn->cows]);
                    if ($turn->bulls === 4) {
                        $win = true;
                        $this->resetState();
                        break;
                    }
                    $this->dumpState($game);
                } catch (GameException $e) {
                    $requestSection->writeln('<error>' . $e->getMessage() . '</error>');
                }
            }
        }
        if ($win) {
            $output->writeln('<fg=green>WIN</>');
        }
        return Command::SUCCESS;
    }

    private function dumpState(Game $game): void
    {
        $state = $game->getState();
        file_put_contents('state.dump', serialize($state));
    }

    private function loadState(Game $game): bool
    {
        if (file_exists('state.dump')) {
            $state = unserialize(file_get_contents('state.dump'));
            $game->loadState($state);
            return true;
        }
        return false;
    }

    private function resetState(): void
    {
        if (file_exists('state.dump')) {
            unlink('state.dump');
        }
    }

    private function initTable(ConsoleSectionOutput $section): Table
    {
        $table = new Table($section);
        $table->setHeaders(['Number', 'Bulls', 'Cows']);
        return $table;
    }
}
