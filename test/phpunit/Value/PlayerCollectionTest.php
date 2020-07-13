<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

use Arp\DominoGame\Exception\DominoGameException;
use Arp\DominoGame\Value\CollectionInterface;
use Arp\DominoGame\Value\Domino;
use Arp\DominoGame\Value\Player;
use Arp\DominoGame\Value\PlayerCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Value\PlayerTest
 */
final class PlayerCollectionTest extends TestCase
{
    /**
     * Assert that the collection implements CollectionInterface.
     *
     * @covers \Arp\DominoGame\Value\PlayerCollection::__construct
     */
    public function testImplementsCollectionInterface(): void
    {
        $collection = new PlayerCollection([]);

        $this->assertInstanceOf(CollectionInterface::class, $collection);
    }

    /**
     * Assert that calls to getOrderedByLowestCount() will return a new collection of players that is ordered
     * by the number of cards they have in ascending order.
     *
     * @covers \Arp\DominoGame\Value\PlayerCollection::getOrderedByLowestCount
     *
     * @throws \Exception
     */
    public function testGetOrderedByLowestCount(): void
    {
        /** @var Player[]|MockObject[] $players */
        $players = [
            $this->createMock(Player::class),
            $this->createMock(Player::class),
            $this->createMock(Player::class),
        ];

        $randomHandCounts = [];
        foreach ($players as $player) {
            $randomHandCount = random_int(1, 28);
            $player->method('getHandCount')->willReturn($randomHandCount);
            $randomHandCounts[] = $randomHandCount;
        }

        $expectedOrders = $randomHandCounts;
        sort($expectedOrders, SORT_NUMERIC);

        $collection = (new PlayerCollection($players))->getOrderedByLowestCount();

        /** @var Player $player */
        foreach ($collection->getElements() as $index => $player) {
            $this->assertSame($expectedOrders[$index], $player->getHandCount());
        }
    }

    /**
     * Assert that getOrderedByHighestDouble() will return a new collection with the elements sorted by players who
     * have hands with the highest double.
     *
     * @param array $data
     *
     * @dataProvider getGetOrderedByHighestDoubleWillReturnANewOrderedCollectionData
     *
     * @covers       \Arp\DominoGame\Value\PlayerCollection::getOrderedByHighestDouble
     */
    public function testGetOrderedByHighestDoubleWillReturnANewOrderedCollection(array $data): void
    {
        $players = [];
        $highestValue = 0;

        foreach ($data as $index => $values) {
            /**
             * @var Player|MockObject $player
             * @var Domino|MockObject $domino
             */
            $player = $this->createMock(Player::class);
            $domino = $this->createMock(Domino::class);

            $value = array_sum($values);
            $expected[$value] = $player;
            if ($value >= $highestValue) {
                $highestValue = $value;
            }

            $player->method('getHighestDouble')->willReturn($domino);
            $domino->method('getValue')->willReturn($value);
            $domino->method('isDouble')->willReturn($values[0] === $values[1]);

            $players[] = $player;
        }
        krsort($expected);
        $expected = array_values($expected);

        $collection = new PlayerCollection($players);

        $orderedCollection = $collection->getOrderedByHighestDouble();

        $this->assertSame($collection->count(), $orderedCollection->count());

        foreach ($orderedCollection as $index => $player) {
            $this->assertSame($expected[$index], $player);
        }
    }

    /**
     * @return array
     */
    public function getGetOrderedByHighestDoubleWillReturnANewOrderedCollectionData(): array
    {
        return [
            [
                [
                    [1, 2],
                    [5, 6],
                    [6, 3],
                ],
            ],
        ];
    }

    /**
     * Assert that a call to getWithLowestHandValue() on an empty player collection will throw a DominoGameException.
     *
     * @covers \Arp\DominoGame\Value\PlayerCollection::getWithLowestHandValue
     *
     * @throws DominoGameException
     */
    public function testGetWithLowestHandValueWillThrowADominoGameExceptionIfCalledOnAnEmptyCollection(): void
    {
        $collection = new PlayerCollection([]);

        $this->expectException(DominoGameException::class);
        $this->expectExceptionMessage('Unable to find player with lowest value with an empty player collection');

        $collection->getWithLowestHandValue();
    }

    /**
     * Assert that when calling getWithLowestGHandValue() we return the single player instance that has the
     * highest total value hand within the collection.
     *
     * @param array $data
     *
     * @covers       \Arp\DominoGame\Value\PlayerCollection::getWithLowestHandValue
     *
     * @dataProvider getGetWithLowestHandValueWillReturnOrderedPlayerCollectionData
     *
     * @throws DominoGameException
     */
    public function testGetWithLowestHandValueWillReturnOrderedPlayerCollection(array $data): void
    {
        /** @var Player[]|MockObject[] $players */
        $lowestHandValue = PHP_INT_MAX;
        $expected = null;
        $players = [];

        foreach ($data as $value) {
            $player = $this->createMock(Player::class);

            $player->method('getHandValue')->willReturn($value);

            $players[] = $player;
            if ($value <= $lowestHandValue) {
                $expected = $player;
                $lowestHandValue = $value;
            }
        }

        $collection = new PlayerCollection($players);
        $result = $collection->getWithLowestHandValue();

        $this->assertSame($expected, $result);
        $this->assertSame($lowestHandValue, $result->getHandValue());
    }

    /**
     * @return array
     */
    public function getGetWithLowestHandValueWillReturnOrderedPlayerCollectionData(): array
    {
        return [
            // Two players
            [
                [7, 8],
            ],
            [
                [10, 7],
            ],
            [
                [1, 9],
            ],

            // Three Players
            [
                [1, 5, 8],
            ],
            [
                [10, 12, 1],
            ],
            [
                [1, 3, 5],
            ],
            [
                [1, 0, 12],
            ],

            // Four players
            [
                [2, 6, 8, 10],
            ],
            [
                [12, 11, 1, 6],
            ],
            [
                [3, 9, 7, 10],
            ],
        ];
    }
}
