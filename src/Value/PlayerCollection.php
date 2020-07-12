<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

use Arp\DominoGame\Exception\DominoGameException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
class PlayerCollection extends AbstractCollection
{
    /**
     * Sort and return a NEW collection sorted by players with the lowest number of dominoes in their hand.
     *
     * @return PlayerCollection
     */
    public function getOrderedByLowestCount(): PlayerCollection
    {
        $elements = $this->elements;

        usort(
            $elements,
            static function (Player $playerA, Player $playerB): int {
                return $playerA->getHandCount() <=> $playerB->getHandCount();
            }
        );

        return new static($elements);
    }

    /**
     * Sort and return a NEW collection sorted by players with the highest double in their hand.
     *
     * @return PlayerCollection
     */
    public function getOrderedByHighestDouble(): PlayerCollection
    {
        $elements = $this->elements;

        usort(
            $elements,
            static function (Player $playerA, Player $playerB): int {
                $doubleA = $playerA->getHighestDouble();
                $doubleB = $playerB->getHighestDouble();

                if (null === $doubleA && null === $doubleB) {
                    return 0;
                }

                if ((null !== $doubleA) && $doubleA->isDouble() && (null === $doubleB || !$doubleB->isDouble())) {
                    return 0;
                }

                if ((null !== $doubleB) && $doubleB->isDouble() && (null === $doubleA || !$doubleA->isDouble())) {
                    return 1;
                }

                return $doubleB->getValue() <=> $doubleA->getValue();
            }
        );

        return new static($elements);
    }

    /**
     * Return the player with the lowest valued sum of all tiles in their hand.
     *
     * @return Player
     *
     * @throws DominoGameException If the collection is empty
     */
    public function getWithLowestHandValue(): Player
    {
        $elements = $this->elements;

        usort(
            $elements,
            static function (Player $playerA, Player $playerB): int {
                return $playerA->getHandValue() <=> $playerB->getHandValue();
            }
        );

        /** @var Player $player */
        $player = (new static($elements))->first();

        if (null === $player) {
            throw new DominoGameException(
                'Unable to find player with lowest value with an empty player collection'
            );
        }

        return $player;
    }
}
