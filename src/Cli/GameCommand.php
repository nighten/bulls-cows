<?php

declare(strict_types=1);

namespace Nighten\Bc\Cli;

use Exception;
use Nighten\Bc\Enum\GameType;
use Nighten\Bc\Exception\GameException;
use Nighten\Bc\Exception\GameIsRunningException;
use Nighten\Bc\Exception\WrongBullCowsValueException;
use Nighten\Bc\Game;
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
    private int $lastPrintedTurn = 0;

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
        $question1 = new Question('Enter number: ');
        $question2 = new Question('Enter bulls and cows: ');
        $requestSection = $output->section();
        $resultSection = $output->section();
        $table = $this->initTable($resultSection);

        $game = GameFactory::create();
        if ($this->gameStateDumper->load($game)) {
            $lastKey = 0;
            foreach ($game->getTurns() as $key => $turn) {
                $table->addRow([
                    ++$key,
                    $turn['user']?->number?->asString() ?? '',
                    $turn['user']?->bulls ?? '',
                    $turn['user']?->cows ?? '',
                    $turn['comp']?->number?->asString() ?? '',
                    $turn['comp']?->bulls ?? '',
                    $turn['comp']?->cows ?? '',
                ]);
                $lastKey = $key;
            }
            $this->lastPrintedTurn = $lastKey;
        } else {
            $game->start(GameType::Comp);
        }

        $table->render();

        $win = false;
        $userWin = false;
        $compWin = false;

        while (true) {
            if ($game->isUserTurn()) {
                $number = $helper->ask($input, $requestSection, $question1);
            } else {
                $compNumber = $game->getCompNumber();
                $requestSection->writeln(
                    'Comp Number: ' . $compNumber->asString(),
                );
                $number = $helper->ask($input, $requestSection, $question2);
            }
            if (!(is_string($number) || null === $number)) {
                throw new Exception('Invalid number. ' . gettype($number) . ' given');
            }
            if ('end' === $number) {
                break;
            }
            if ('new' === $number) {
                $this->lastPrintedTurn = 0;
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
                    if ($game->isUserTurn()) {
                        $turn = $game->userTurn($number);
                        $requestSection->writeln(
                            'Turn: ' . $number . ' Bulls: ' . $turn->bulls . ' Cows: ' . $turn->cows
                        );

                        if ($turn->bulls === 4) {
                            $win = true;
                            $userWin = true;
                            $this->gameStateDumper->reset();
                            break;
                        }
                    } else {
                        if (strlen($number) !== 2) {
                            throw new WrongBullCowsValueException('Expected 2 number');
                        }
                        $bulls = (int)$number[0];
                        $cows = (int)$number[1];
                        if ($bulls > 4 || $cows > 4 || ($bulls + $cows) > 4) {
                            throw new WrongBullCowsValueException('Bulls and cows must be less than 4');
                        }
                        $requestSection->writeln(
                            'Bulls: ' . $bulls . ' Cows: ' . $cows
                        );
                        $game->getCompNumberAnswer($bulls, $cows);

                        if ($bulls === 4) {
                            $win = true;
                            $compWin = true;
                            $this->gameStateDumper->reset();
                            break;
                        }
                    }

                    $this->gameStateDumper->dump($game);
                } catch (GameException $e) {
                    $requestSection->writeln('<error>' . $e->getMessage() . '</error>');
                }

                $this->addPrintTurnIfFinished($game, $table);
            }
        }
        if ($win) {
            if ($userWin) {
                $output->writeln('<fg=green>You WIN</>');
            } elseif ($compWin) {
                $output->writeln('<fg=green>Comp WIN</>');
            } else {
                $output->writeln('<fg=green>Somebody WIN</>');
            }
        }
        return Command::SUCCESS;
    }

    private function initTable(ConsoleSectionOutput $section): Table
    {
        $table = new Table($section);
        $table->setHeaders(['Turn', 'Your number', 'Bulls', 'Cows', 'Comp number', 'Bulls', 'Cows']);
        return $table;
    }

    private function addPrintTurnIfFinished(Game $game, Table $table): void
    {
        if (!$game->getLastTurn()) {
            return;
        }
        $lastTurnCount = $game->getTurnCount();
        if ($lastTurnCount > $this->lastPrintedTurn) {
            $lastTurn = $game->getLastTurn();
            $table->appendRow([
                $game->getTurnCount(),
                $lastTurn['user']?->number->asString() ?? '',
                $lastTurn['user']?->bulls ?? '',
                $lastTurn['user']?->cows ?? '',
                $lastTurn['comp']?->number->asString() ?? '',
                $lastTurn['comp']?->bulls ?? '',
                $lastTurn['comp']?->cows ?? '',
            ]);
            $this->lastPrintedTurn = $lastTurnCount;
        }
    }
}
