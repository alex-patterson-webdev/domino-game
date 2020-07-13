<?php

declare(strict_types=1);

namespace Arp\DominoGame;

use Arp\DominoGame\Exception\DominoGameException;
use Arp\DominoGame\Exception\InvalidArgumentException;
use Arp\DominoGame\Value\Board;
use Arp\DominoGame\Value\Domino;
use Arp\DominoGame\Value\DominoCollection;
use Arp\DominoGame\Value\Player;
use Arp\DominoGame\Value\PlayerCollection;
use Psr\Log\LoggerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame
 */
final class DominoGame
{
    /**
     * @var Board
     */
    private Board $board;

    /**
     * Collection of all the players playing the game.
     *
     * @var PlayerCollection|Player[]
     */
    private PlayerCollection $players;

    /**
     * A collection of Dominoes that are available to be picked by players.
     *
     * @var DominoCollection
     */
    private DominoCollection $deck;

    /**
     * A PSR logger that will record the game actions
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param PlayerCollection $players
     * @param LoggerInterface  $logger
     * @param int              $maxTileSize
     *
     * @throws DominoGameException If the game cannot be created
     */
    public function __construct(PlayerCollection $players, LoggerInterface $logger, int $maxTileSize)
    {
        $this->logger = $logger;
        $this->board = new Board();

        $this->reset($players, $maxTileSize);
    }

    /**
     * Deal a new set of dominoes to the players. This will remove any existing dominoes from the players.
     *
     * @param int $handSize The number of dominoes to deal to each player.
     *
     * @throws DominoGameException
     */
    public function deal(int $handSize): void
    {
        if ($handSize < 1) {
            throw new DominoGameException('Hand sizes must be a minimum of 1');
        }

        $deckCount = $this->deck->count();
        $playerCount = $this->players->count();

        if ($deckCount < ($handSize * $playerCount)) {
            throw new DominoGameException(
                sprintf(
                    'The hand size %d exceeds the maximum permissible for current deck size of %d',
                    $handSize,
                    $deckCount
                )
            );
        }

        // Ensure that we randomise the deck before dealing a domino to each player in turn
        $this->deck->shuffle();

        $this->logger->info(sprintf('Deck shuffled: %s', (string)$this->deck));
        $this->logger->info(
            sprintf('Dealing a hand size of %d dominoes to %d players', $handSize, $playerCount)
        );

        foreach ($this->deck as $domino) {
            foreach ($this->players->getOrderedByLowestCount() as $player) {
                if ($player->getHandCount() < $handSize) {
                    $player->addToHand($domino);
                    $this->deck->removeElement($domino);

                    $this->logger->info(
                        sprintf('\'%s\' was dealt domino \'%s\'', (string)$player, (string)$domino)
                    );

                    // Move on to next domino
                    continue 2;
                }
            }
        }
    }

    /**
     * Run the game and return the winner.
     *
     * @param int $handSize
     *
     * @return Player
     *
     * @throws DominoGameException
     * @throws InvalidArgumentException
     */
    public function run(int $handSize): ?Player
    {
        $this->deal($handSize);
        $this->logSummary();

        do {
            $winner = $this->takeTurns();
        } while (null === $winner);

        return $winner;
    }

    /**
     * Reset the game scores to zero and prepare a new boneyard so a new game can be played.
     *
     * @param PlayerCollection|Player[] $players
     * @param int                       $maxTileSize
     *
     * @throws DominoGameException If the game cannot be reset
     */
    public function reset(PlayerCollection $players, int $maxTileSize): void
    {
        $playerCount = $players->count();

        if ($playerCount < 2 || $playerCount > 4) {
            throw new DominoGameException(
                sprintf('There must be a minimum of 1 and a maximum of 4 players; %d provided', $playerCount)
            );
        }

        $this->logger->info('Resetting game');
        foreach ($players as $player) {
            $player->getHand()->removeElements(null);
        }

        $this->deck = $this->createDominoCollection($maxTileSize);
        $this->players = $players;
    }

    /**
     * @return PlayerCollection
     */
    public function getPlayers(): PlayerCollection
    {
        return $this->players;
    }

    /**
     * @return DominoCollection
     */
    public function getDeck(): DominoCollection
    {
        return $this->deck;
    }

    /**
     * Each player executes a single turn. The first placement on the board will require the
     * player with the highest double to place a single piece.
     *
     * @return Player|null
     *
     * @throws InvalidArgumentException
     * @throws DominoGameException
     */
    private function takeTurns(): ?Player
    {
        $players = $this->board->isEmpty()
            ? $this->players->getOrderedByHighestDouble()
            : $this->players;

        foreach ($players as $player) {
            $winner = $this->takeTurn($player);

            if (null !== $winner) {
                $this->logger->info(
                    sprintf(
                        '\'%s\' is the winner with the lowest hand value of \'%d\'',
                        (string)$winner,
                        $winner->getHandValue()
                    )
                );
                return $winner;
            }

            if (null === $winner && 0 === $player->getHandCount()) {
                $this->logger->info(
                    sprintf(
                        '\'%s\' is the winner with 0 cards left to play',
                        (string)$player
                    )
                );
                return $player;
            }
        }

        $this->logSummary();
        return null;
    }

    /**
     * Perform a single turn for the provided player.
     *
     * @param Player $player
     *
     * @return Player|null
     *
     * @throws DominoGameException
     * @throws InvalidArgumentException
     */
    private function takeTurn(Player $player): ?Player
    {
        $domino = $player->getDominoWithMatchingTile($this->board);
        if (null === $domino) {
            $this->logger->info(sprintf('\'%s\' was unable to find a matching domino', (string)$player));

            // If there are no more dominoes to pick, we need to resolve the winner
            if ($this->deck->isEmpty()) {
                $this->logger->info(
                    sprintf(
                        '\'%s\' has no more dominoes available to pick from the deck.'
                        . 'Determining the winner from the lowest total score from each players hand',
                        (string)$player
                    )
                );
                return $this->players->getWithLowestHandValue();
            }

            // We could not find a matching tile to place so we have to pick up another
            $randomPick = $this->deck->pickRandom();
            $player->addToHand($randomPick);

            $this->logger->info(
                sprintf(
                    '\'%s\' has picked domino %s from the deck',
                    (string)$player,
                    (string)$randomPick
                )
            );
            return null;
        }

        $this->logger->info(sprintf('\'%s\' has placed domino %s', (string)$player, (string)$domino));

        $this->board->place($domino);
        $player->removeFromHand($domino);

        return null;
    }

    /**
     * Generate the Domino collection (the 'boneyard') that players will pick from.
     *
     * @param int $maxTileSize
     *
     * @return DominoCollection
     */
    private function createDominoCollection(int $maxTileSize): DominoCollection
    {
        $collection = new DominoCollection();

        for ($x = 0; $x <= $maxTileSize; $x++) {
            for ($y = $x; $y <= $maxTileSize; $y++) {
                $collection->add(new Domino($x, $y));
            }
        }

        return $collection;
    }

    /**
     * Log a summary of the current state of the board and players hands
     */
    private function logSummary(): void
    {
        if (!$this->deck->isEmpty()) {
            $this->logger->info('Remaining tiles: ' . (string)$this->deck);
        }

        foreach ($this->players as $player) {
            $this->logger->info(
                sprintf('\'%s\' hand: %s', (string)$player, (string)$player->getHand())
            );
        }
    }
}
