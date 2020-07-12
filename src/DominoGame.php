<?php

declare(strict_types=1);

namespace Arp\DominoGame;

use Arp\DominoGame\Exception\DominoGameException;
use Arp\DominoGame\Exception\InvalidArgumentException;
use Arp\DominoGame\Value\Board;
use Arp\DominoGame\Value\Domino;
use Arp\DominoGame\Value\DominoCollection;
use Arp\DominoGame\Value\PlayerCollection;
use Arp\DominoGame\Value\PlayerInterface;

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
     * @var PlayerCollection|PlayerInterface[]
     */
    private PlayerCollection $players;

    /**
     * A collection of Dominoes that are available to be picked by players.
     *
     * @var DominoCollection
     */
    private DominoCollection $deck;

    /**
     * @param PlayerCollection $players
     * @param int              $maxTileSize
     *
     * @throws DominoGameException If the game cannot be created
     */
    public function __construct(PlayerCollection $players, int $maxTileSize)
    {
        $this->board = new Board();

        $this->reset($players, $maxTileSize);
    }

    /**
     * Deal a new set of dominoes to the players. This will remove any existing dominoes from the players.
     *
     * @param int $handSize  The number of dominoes to deal to each player.
     *
     * @throws DominoGameException
     */
    public function deal(int $handSize): void
    {
        if ($handSize < 1) {
            throw new DominoGameException('Hand sizes must be a minimum of 1');
        }

        $deckCount = $this->deck->count();
        if ($deckCount < ($handSize * $this->players->count())) {
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
        foreach ($this->deck as $domino) {
            foreach ($this->players->getOrderedByLowestCount() as $player) {
                if ($player->getHandCount() < $handSize) {
                    $player->addToHand($domino);
                    $this->deck->removeElement($domino);
                    // Move on to next domino
                    continue 2;
                }
            }
        }
    }

    /**
     * Run the game and return the winner.
     *
     * @return PlayerInterface
     *
     * @throws DominoGameException
     * @throws InvalidArgumentException
     */
    public function run(): ?PlayerInterface
    {
        $player = null;

        do {
            $player = $this->takeTurns();
        } while (null === $player);

        return $player;
    }

    /**
     * Each player executes a single turn. The first placement on the board will require the
     * player with the highest double to place a single piece.
     *
     * @return PlayerInterface|null
     *
     * @throws InvalidArgumentException
     * @throws DominoGameException
     */
    private function takeTurns(): ?PlayerInterface
    {
        $players = $this->board->isEmpty()
            ? $this->players->getOrderedByHighestDouble()
            : $this->players;

        foreach ($players as $player) {
            $matchingDomino = $player->getDominoWithMatchingTile($this->board);

            if (null === $matchingDomino) {
                // If there are no more dominoes to pick, we need to resolve the winner
                if ($this->deck->isEmpty()) {
                    return $players->getWithLowestHandValue();
                }
                // We could not find a matching tile to place so we have to pick up another
                $this->pickFromDeck($player);
                continue;
            }

            if (null !== $matchingDomino && $this->board->place($matchingDomino)) {
                $player->removeFromHand($matchingDomino);

                if (0 === $player->getHandCount()) {
                    return $player;
                }
            }
        }

        return null;
    }

    /**
     * Pick up a tile from the deck and add it to the players hand.
     *
     * @param PlayerInterface $player The player who is picking up a domino.
     *
     * @throws DominoGameException If the deck is empty
     */
    private function pickFromDeck(PlayerInterface $player): void
    {
        if ($this->deck->isEmpty()) {
            throw new DominoGameException(
                sprintf('%s cannot pick tiles from an empty table', $player->getName())
            );
        }

        $player->addToHand($this->deck->pickRandom());
    }

    /**
     * Reset the game scores to zero and prepare a new boneyard so a new game can be played.
     *
     * @param PlayerCollection|PlayerInterface[] $players
     * @param int                                $maxTileSize
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
}
