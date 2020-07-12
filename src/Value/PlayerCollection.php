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
            static function (PlayerInterface $playerA, PlayerInterface $playerB): int {
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
            static function (PlayerInterface $playerA, PlayerInterface $playerB): int {
                return $playerA->getHighestDouble() <=> $playerB->getHighestDouble();
            }
        );

        return new static($elements);
    }

    /**
     * Return the player with the lowest valued sum of all tiles in their hand.
     *
     * @return PlayerInterface
     *
     * @throws DominoGameException If the collection is empty
     */
    public function getWithLowestHandValue(): PlayerInterface
    {
        $elements = $this->elements;

        usort(
            $elements,
            static function (PlayerInterface $playerA, PlayerInterface $playerB): int {
                return $playerA->getHandValue() <=> $playerB->getHandValue();
            }
        );

        /** @var PlayerInterface $player */
        $player = (new static($elements))->first();

        if (null === $player) {
            throw new DominoGameException(
                'Unable to find player with lowest value with an empty player collection'
            );
        }

        return $player;
    }
}
