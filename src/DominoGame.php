<?php

declare(strict_types=1);

namespace Arp\DominoGame;

use Arp\DominoGame\Exception\DominoGameException;
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

        // Ensure that we randomise the deck and loop the randomised deck
        // deal a domino to each player in turn, until they hit their limit
        $this->deck->shuffle();
        foreach ($this->deck as $domino) {
            foreach ($this->players as $player) {
                if ($handSize > $player->getHandSize()) {
                    $player->addToHand($domino);
                    // It's vital that we do not add the same domino to each player
                    continue 2;
                }
            }
        }
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

        $this->players = $players;
        $this->deck = $this->createDominoCollection($maxTileSize);
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