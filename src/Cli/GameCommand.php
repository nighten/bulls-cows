<?php

declare(strict_types=1);

namespace Nighten\Bc\Cli;

use Exception;
use Nighten\Bc\Exception\GameException;
use Nighten\Bc\Exception\GameIsRunningException;
use Nighten\Bc\GameFactory;
use Nighten\Bc\Service\GameStateDumper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
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
    public function __construct(
        private readonly GameStateDumper $gameStateDumper,
    ) {
        parent::__construct();
    }

    /**
     * @throws GameIsRunningException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new Exception(
                'This command requires a console output instance of ConsoleOutputInterface.'
            );
        }

        $output->writeln('<info>Starting game Bulls and Cows</info>');
        $output->writeln('<info>For end game type "end"</info>');
        $output->writeln('<info>For start new game type "new"</info>');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Enter number: ');
        $requestSection = $output->section();
        $resultSection = $output->section();
        $table = $this->initTable($resultSection);

        $game = GameFactory::create();
        if ($this->gameStateDumper->load($game)) {
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
            if (!(is_string($number) || null === $number)) {
                throw new Exception('Invalid number. ' . gettype($number) . ' given');
            }
            if ('end' === $number) {
                break;
            }
            if ('new' === $number) {
                $this->gameStateDumper->reset();
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
                        $this->gameStateDumper->reset();
                        break;
                    }
                    $this->gameStateDumper->dump($game);
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

    private function initTable(ConsoleSectionOutput $section): Table
    {
        $table = new Table($section);
        $table->setHeaders(['Number', 'Bulls', 'Cows']);
        return $table;
    }
}
