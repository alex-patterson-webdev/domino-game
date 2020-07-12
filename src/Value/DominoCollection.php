<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

use Arp\DominoGame\Exception\DominoGameException;

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

    /**
     * Add a domino to the start of the collection.
     *
     * @param Domino $domino
     */
    public function addToStart(Domino $domino): void
    {
        array_unshift($this->elements, $domino);
    }

    /**
     * Add a domino to the end of the collection.
     *
     * @param Domino $domino
     */
    public function addToEnd(Domino $domino): void
    {
        $this->elements[] = $domino;
    }

    /**
     * Return the summed value of all the tiles in the player's hand
     *
     * @return int
     */
    public function getValue(): int
    {
        $value = 0;

        /** @var Domino $domino */
        foreach ($this->elements as $domino) {
            $value += $domino->getValue();
        }

        return $value;
    }

    /**
     * Return a single domino with the highest value (if there are matching values the order is undefined)
     *
     * @return Domino|null
     */
    public function getDominoWithHighestValue(): ?Domino
    {
        $sortedCollection = $this->createCollectionSortedByHighestTileValue();

        if ($sortedCollection->isEmpty()) {
            return null;
        }

        /** @var Domino|null $domino */
        $domino = $sortedCollection->first();
        return $domino;
    }

    /**
     * Create a new DominoCollection instance with the $elements sorted by the highest tile (sum) values.
     *
     * @return DominoCollection
     */
    public function createCollectionSortedByHighestTileValue(): DominoCollection
    {
        $elements = $this->elements;

        uasort(
            $elements,
            static function (Domino $dominoA, Domino $dominoB) {
                return $dominoA->getValue() <=> $dominoB->getValue();
            }
        );

        return new static($elements);
    }

    /**
     * Create a new collection containing any dominoes that have tile values matching the provided $value.
     *
     * @param int $value The value that should be matched.
     *
     * @return DominoCollection
     */
    public function createCollectionWithMatchingTiles(int $value): DominoCollection
    {
        $matches = [];

        /** @var Domino $domino */
        foreach ($this->elements as $domino) {
            if ($value === $domino->getTopTile() || $value === $domino->getBottomTile()) {
                $matches[] = $domino;
            }
        }

        return new static($matches);
    }

    /**
     * Pick a random domino from the collection.
     *
     * @return Domino
     *
     * @throws DominoGameException If the random pick is impossible or removal from the collection fails
     */
    public function pickRandom(): Domino
    {
        if ($this->isEmpty()) {
            throw new DominoGameException('Unable to pick a domino from an empty collection');
        }

        try {
            $domino = $this->elements[random_int(0, count($this->elements) - 1)];
        } catch (\Throwable $e) {
            throw new DominoGameException(
                sprintf('Failed to pick a random domino from the collection: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        if (!$this->removeElement($domino)) {
            throw new DominoGameException('Failed to remove the picked domino from the collection');
        }

        return $domino;
    }

    /**
     * Return the collection represented as a string.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';

        foreach ($this->elements as $element) {
            $string .= $element . ' ';
        }

        return trim($string);
    }
}
