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

        return new PlayerCollection($elements);
    }
}
