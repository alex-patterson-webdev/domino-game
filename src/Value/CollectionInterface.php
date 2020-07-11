<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
interface CollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return iterable
     */
    public function getElements(): iterable;

    /**
     * @param iterable $elements
     */
    public function setElements(iterable $elements);

    /**
     * @param iterable $elements
     */
    public function addElements(iterable $elements);

    /**
     * Remove all elements from the collection.
     *
     * @param iterable|null $elements Optional collection of elements to remove.
     *
     * @return mixed
     */
    public function removeElements(?iterable $elements);
}