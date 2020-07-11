<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
interface PlayerInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Return the number of dominoes remaining in the players hand.
     *
     * @return int
     */
    public function getHandSize(): int;

    /**
     * Return the domino collection owned by the player.
     *
     * @return DominoCollection
     */
    public function getHand(): DominoCollection;

    /**
     * @param DominoCollection$dominoCollection
     */
    public function setHand(DominoCollection $dominoCollection): void;

    /**
     * Add a single domino to the players hand
     *
     * @param Domino $domino
     */
    public function addToHand(Domino $domino): void;
}
