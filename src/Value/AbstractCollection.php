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
     * @return iterable
     */
    public function getElements(): iterable
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
}
