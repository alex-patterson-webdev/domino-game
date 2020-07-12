<?php

declare(strict_types=1);

namespace Arp\DominoGame\Value;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\DominoGame\Value
 */
abstract class AbstractCollection implements CollectionInterface
{
    /**
     * The elements within the collection.
     *
     * @var array
     */
    protected array $elements = [];

    /**
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->setElements($elements);
    }

    /**
     * Return an iterable representation of the collection.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * @param iterable $elements
     */
    public function setElements(iterable $elements): void
    {
        $this->removeElements($this->elements);
        $this->addElements($elements);
    }

    /**
     * @param iterable $elements
     */
    public function addElements(iterable $elements): void
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }
    }

    /**
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Remove an element from the collection.
     *
     * @param object $element
     *
     * @return bool
     */
    public function removeElement(object $element): bool
    {
        $index = array_search($element, $this->elements, true);

        if (false === $index) {
            return false;
        }
        unset($this->elements[$index]);
        return true;
    }

    /**
     * Remove elements from the collection.
     *
     * @param iterable|null $elements
     *
     * @return mixed|void
     */
    public function removeElements(?iterable $elements)
    {
        if (null === $elements) {
            $elements = $this->elements;
        }
        foreach ($elements as $element) {
            $index = array_search($element, $this->elements, true);
            if (false !== $index) {
                unset($this->elements[$index]);
            }
        }
    }

    /**
     * Return the first element in the collection.
     *
     * @return object|null
     */
    public function first(): ?object
    {
        return $this->elements[0] ?? null;
    }

    /**
     * Return the last element in the collection.
     *
     * @return object|null
     */
    public function last(): ?object
    {
        return $this->elements[count($this->elements)-1] ?? null;
    }
}
