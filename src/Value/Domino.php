<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
class Domino
{
    /**
     * @var int
     */
    private int $topTile;

    /**
     * @var int
     */
    private int $bottomTile;

    /**
     * @param int $topTile
     * @param int $bottomTile
     */
    public function __construct(int $topTile, int $bottomTile)
    {
        $this->topTile = $topTile;
        $this->bottomTile = $bottomTile;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->topTile . '-' . $this->bottomTile;
    }

    /**
     * Check if the top and bottom dots are equal, a double.
     *
     * @return bool
     */
    public function isDouble(): bool
    {
        return $this->topTile === $this->bottomTile;
    }

    /**
     * Return the number of dots in the 'top' square.
     *
     * @return int
     */
    public function getTopTile(): int
    {
        return $this->topTile;
    }

    /**
     * Return the number of dots in the 'bottom' square.
     *
     * @return int
     */
    public function getBottomTile(): int
    {
        return $this->bottomTile;
    }

    /**
     * Return the sum of the domino's dots.
     *
     * @return int
     */
    public function getValue(): int
    {
        return ($this->topTile + $this->bottomTile);
    }

    /**
     * Check if both provided $topTitle and $bottomTile values match the current domino values.
     *
     * @param int $topTitle
     * @param int $bottomTile
     *
     * @return bool
     */
    public function isTileMatch(int $topTitle, int $bottomTile): bool
    {
        return $this->isTopTileMatch($topTitle) && $this->isBottomTitleMatch($bottomTile);
    }

    /**
     * @param int $topTitle
     *
     * @return bool
     */
    public function isTopTileMatch(int $topTitle): bool
    {
        return $this->topTile === $topTitle;
    }

    /**
     * @param int $bottomTile
     *
     * @return bool
     */
    public function isBottomTitleMatch(int $bottomTile): bool
    {
        return $this->bottomTile === $bottomTile;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
