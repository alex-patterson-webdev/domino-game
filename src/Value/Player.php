<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

use Arp\DominoGame\Exception\DominoGameException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
final class Player implements PlayerInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var DominoCollection
     */
    private DominoCollection $hand;

    public function __construct()
    {
        $this->hand = new DominoCollection([]);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DominoCollection
     */
    public function getHand(): DominoCollection
    {
        return $this->hand;
    }

    /**
     * @param DominoCollection $hand
     */
    public function setHand(DominoCollection $hand): void
    {
        $this->hand = $hand;
    }

    /**
     * @return int
     */
    public function getHandCount(): int
    {
        return $this->hand->count();
    }

    /**
     * Return the total value of the entire hand
     *
     * @return int
     */
    public function getHandValue(): int
    {
        return $this->hand->getValue();
    }

    /**
     * @param Domino $domino
     */
    public function addToHand(Domino $domino): void
    {
        $this->hand->add($domino);
    }

    /**
     * Remove a domino from the players hand.
     *
     * @param Domino $domino
     *
     * @return bool
     *
     * @throws DominoGameException If the $domino provided does not exist in the players hand
     */
    public function removeFromHand(Domino $domino): bool
    {
        if (!$this->hand->has($domino)) {
            throw new DominoGameException(
                sprintf(
                    'The player \'%s\' does not have the domino \'%s\' to remove',
                    $this->getName(),
                    $domino->getName()
                )
            );
        }

        return $this->hand->removeElement($domino);
    }

    /**
     * Find and return the domino that has the highest double in the hand. If there are no doubles then null is returned
     *
     * @return Domino|null
     */
    public function getHighestDouble(): ?Domino
    {
        if (0 === $this->hand->count()) {
            return null;
        }

        $hand = $this->hand->getElements();
        usort(
            $hand,
            static function (Domino $a, Domino $b) {
                if ($a->isDouble() && !$b->isDouble()) {
                    return 1;
                }
                if (!$a->isDouble() && $b->isDouble()) {
                    return 0;
                }
                return $a->getValue() <=> $b->getValue();
            }
        );

        return $hand[0] ?? null;
    }

    /**
     * Attempt to find domino that 'matches' the available tiles in the provided $board.
     *
     * There will be cases where we can match 0 tiles (not right or left provided by the board)
     * There will be cases where we can match 1 tiles (right or left)
     * There will be cases where we can match 2 tiles (right and left)
     *  - This can be with 1 tile
     *  - This can be with more than one tile (so multiple possible choices)
     *
     * In all above cases we aim to return a SINGLE domino that matches either left OR right if possible. If we end
     * up having to choose between more than one match, we will always choose the domino with the HIGHEST value, as
     * this would ultimately result in use having a lower overall value in our hand.
     *
     * @param Board $board
     *
     * @return Domino|null
     */
    public function getDominoWithMatchingTile(Board $board): ?Domino
    {
        // If there is an empty board, fetch our highest available double...
        if ($board->isEmpty()) {
            return $this->getHighestDouble();
        }

        $leftTile = $board->getLeftTile();
        $rightTile = $board->getRightTile();

        $matchesLeft = $this->hand->createCollectionWithMatchingTiles($leftTile);
        $matchesRight = $this->hand->createCollectionWithMatchingTiles($rightTile);

        // No matches found in our hand
        if ($matchesLeft->isEmpty() && $matchesRight->isEmpty()) {
            return null;
        }

        // If the left is the highest value, we will try to place our piece on the left
        if ($leftTile > $rightTile && !$matchesLeft->isEmpty()) {
            return $matchesLeft->getDominoWithHighestValue();
        }

        // If the right is the highest value, we will try to place our piece on the right
        if ($rightTile > $leftTile && !$matchesRight->isEmpty()) {
            return $matchesRight->getDominoWithHighestValue();
        }

        /**
         * Now we are left with both left and right matching so we can merge the collections
         * together and return the single largest valued domino.
         *
         * @var DominoCollection $mergedCollection
         */
        $mergedCollection = $matchesLeft->createMergedCollection($matchesRight);

        return $mergedCollection->getDominoWithHighestValue();
    }
}
