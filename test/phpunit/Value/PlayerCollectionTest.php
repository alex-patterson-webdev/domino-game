<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

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
     * @covers \Arp\DominoGame\Value\PlayerCollection::getOrderedByHighestDouble
     */
    public function testGetOrderedByHighestDoubleWillReturnANewOrderedCollection(): void
    {
        $fred = new Player('Fred');
        $fred->addToHand(new Domino(1, 2));
        $fred->addToHand(new Domino(5, 5));
        $fred->addToHand(new Domino(3, 2));
        $fred->addToHand(new Domino(3, 3));

        $bob = new Player('Bob');
        $bob->addToHand(new Domino(1, 1));
        $bob->addToHand(new Domino(2, 2));
        $bob->addToHand(new Domino(4, 4));

        $jennifer = new Player('Jennifer');
        $jennifer->addToHand(new Domino(6, 6));
        $jennifer->addToHand(new Domino(2, 2));

        $players = [$fred, $bob, $jennifer];
        $expected = [$jennifer, $fred, $bob];

        $collection = new PlayerCollection($players);

        $orderedCollection = $collection->getOrderedByHighestDouble();

        $this->assertSame($collection->count(), $orderedCollection->count());

        foreach ($orderedCollection as $index => $player) {
            $this->assertSame($expected[$index], $player);
        }
    }
}
