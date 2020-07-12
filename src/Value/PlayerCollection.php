<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
class PlayerCollection extends AbstractCollection
{
    /**
     * Check if we have any players in the collection who have 0 pieces remaining (the winner).
     *
     * @return bool
     */
    public function hasWinner(): bool
    {
        /** @var PlayerInterface $element */
        foreach ($this->elements as $element) {
            if (0 === $element->getHandCount()) {
                return true;
            }
        }
        return false;
    }

    /**
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
}
