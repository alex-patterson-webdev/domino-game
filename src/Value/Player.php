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
    public function getHandSize(): int
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
}
