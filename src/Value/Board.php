<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

use Arp\DominoGame\Exception\InvalidArgumentException;

/**
 * Value object that represents the placed dominoes of the game. Internally we manage a collection of dominoes that have
 * been matched by the number of dots on the exposed tile.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
class Board
{
    /**
     * The 'start' of our stack is treated as the 'left' and the 'end' is treated as the right.
     *
     * @var DominoCollection
     */
    private DominoCollection $placed;

    /**
     * In memory representation of the exposed domino tiles.
     *
     * @var Domino|null
     */
    private ?Domino $virtualDomino;

    /**
     * Prepare the board dependencies.
     */
    public function __construct()
    {
        $this->placed = new DominoCollection();
        $this->virtualDomino = null;
    }

    /**
     * Check if the board is empty (zero placed pieces)
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return (0 === $this->placed->count());
    }

    /**
     * Return the placed dominoes collection.
     *
     * @return DominoCollection
     */
    public function getPlaced(): DominoCollection
    {
        return $this->placed;
    }

    /**
     * Place the provided domino on the board. If we have existing pieces on the board we generate a 'virtual' domino
     * that represents both left and right tiles that can be matches against.
     *
     * If the provided domino matches
     *
     * @param Domino $domino
     *
     * @return bool
     *
     * @throws InvalidArgumentException If the provided domino is invalid
     */
    public function place(Domino $domino): bool
    {
        // If there is no virtual domino we can directly add to the board as it is our first piece.
        if (0 === $this->placed->count()) {
            if (!$domino->isDouble()) {
                throw new InvalidArgumentException(
                    sprintf('The first domino place must be a double \'%s\' provided', $domino->getName())
                );
            }
            $this->placed->addToStart($domino);
            $this->virtualDomino = new Domino($domino->getTopTile(), $domino->getBottomTile());
            return true;
        }

        $leftTile = $this->virtualDomino->getTopTile();
        $rightTile = $this->virtualDomino->getBottomTile();

        if ($leftTile > $rightTile && $this->placeLeft($domino)) {
            return true;
        }

        if ($rightTile > $leftTile && $this->placeRight($domino)) {
            return true;
        }

        if ($this->placeLeft($domino) || $this->placeRight($domino)) {
            return true;
        }

        throw new InvalidArgumentException(
            sprintf(
                'The domino \'%s\' cannot be placed on the board; tiles must match values \'%d\' or \'%d\'',
                $domino->getName(),
                $this->getLeftTile(),
                $this->getRightTile()
            )
        );
    }

    /**
     * Attempt to place the provided domino with the left side of the board's pieces. We can flip the domino
     * both ways so we should check both ways before updating the board.
     *
     * @param Domino $domino The domino that is being placed.
     *
     * @return bool
     */
    private function placeLeft(Domino $domino): bool
    {
        $leftTitle = $this->virtualDomino->getTopTile();    // Represents left placement
        $rightTile = $this->virtualDomino->getBottomTile(); // Represents right placement

        // Does the top tile match the left side of the placed pieces?
        if ($domino->getTopTile() === $leftTitle) {
            $this->placed->addToStart($domino);
            $this->virtualDomino = new Domino($domino->getBottomTile(), $rightTile);
            return true;
        }

        // Does the bottom tile match the left side of the placed pieces?
        if ($domino->getBottomTile() === $leftTitle) {
            $this->placed->addToStart($domino);
            $this->virtualDomino = new Domino($domino->getTopTile(), $rightTile);
            return true;
        }

        return false;
    }

    /**
     * Attempt to place the provided domino with the right side of the board's pieces. We can flip the domino
     * both ways so we should check both ways before updating the board.
     *
     * @param Domino $domino The domino that is being placed.
     *
     * @return bool
     */
    private function placeRight(Domino $domino): bool
    {
        $rightTile = $this->virtualDomino->getBottomTile(); // Represents right placement
        $leftTitle = $this->virtualDomino->getTopTile();    // Represents left placement

        // Does the top tile match the right side of the placed pieces?
        if ($domino->getTopTile() === $rightTile) {
            $this->placed->addToEnd($domino);
            $this->virtualDomino = new Domino($leftTitle, $domino->getBottomTile());
            return true;
        }

        // Does the bottom tile match the right side of the placed pieces?
        if ($domino->getBottomTile() === $rightTile) {
            $this->placed->addToEnd($domino);
            $this->virtualDomino = new Domino($leftTitle, $domino->getTopTile());
            return true;
        }

        return false;
    }

    /**
     * Remove all elements from the board and reset the virtual domino to null.
     */
    public function clearBoard(): void
    {
        $this->placed->removeElements(null);
        $this->virtualDomino = null;
    }

    /**
     * Return the domino that is places on the left most position (first position in the collection).
     *
     * @return Domino|null
     */
    public function getLeft(): ?Domino
    {
        /** @var Domino $domino */
        $domino = $this->placed->first();
        return $domino;
    }

    /**
     * Return the value of the tile that is exposed in the left most position. This is one of the targets that players
     * must match a double against and matches the value of the 'top' tile.
     *
     * @return int|null
     */
    public function getLeftTile(): ?int
    {
        return isset($this->virtualDomino) ? $this->virtualDomino->getTopTile() : null;
    }

    /**
     * Return the domino that is places on the right most position (last position in the collection).
     *
     * @return Domino|null
     */
    public function getRight(): ?Domino
    {
        /** @var Domino $domino */
        $domino = $this->placed->last();

        return $domino;
    }

    /**
     * Return the value of the tile that is exposed in the right most position. This is one of the targets that players
     * must match a double against and matches the value of the 'bottom' tile.
     *
     * @return int|null
     */
    public function getRightTile(): ?int
    {
        return isset($this->virtualDomino) ? $this->virtualDomino->getBottomTile() : null;
    }
}
