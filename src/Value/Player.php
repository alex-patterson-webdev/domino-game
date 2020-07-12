<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

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
     * @param Domino $domino
     */
    public function addToHand(Domino $domino): void
    {
        $this->hand->add($domino);
    }

    /**
     * Find and return the domino that has the highest double in the hand. If there are no doubles then null is
     * returned.
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

}
