<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
class DominoCollection extends AbstractCollection
{
    /**
     * Return the domino matching the provided left and right dot values.
     *
     * @param int $topTile
     * @param int $bottomTile
     *
     * @return Domino|null
     */
    public function get(int $topTile, int $bottomTile): ?Domino
    {
        /** @var Domino $domino */
        foreach ($this->elements as $domino) {
            if ($domino->isTileMatch($topTile, $bottomTile)) {
                return $domino;
            }
        }

        // @todo Consider if this is acceptable
        return null;
    }

    /**
     * @param Domino $domino
     *
     * @return bool
     */
    public function has(Domino $domino): bool
    {
        return in_array($domino, $this->elements, true);
    }

    /**
     * Add a new domino to the collection.
     *
     * @param Domino $domino
     */
    public function add(Domino $domino): void
    {
        if (!$this->has($domino)) {
            $this->elements[] = $domino;
        }
    }

    /**
     * Randomly shuffle the collection elements
     *
     * @return bool
     */
    public function shuffle(): bool
    {
        return shuffle($this->elements);
    }


    public function splice(): DominoCollection
    {

    }

    public function merge(DominoCollection $dominoCollection)
    {

    }
}
