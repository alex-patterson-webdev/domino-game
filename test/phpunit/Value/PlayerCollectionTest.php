<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

use Arp\DominoGame\Value\CollectionInterface;
use Arp\DominoGame\Value\PlayerCollection;
use Arp\DominoGame\Value\PlayerInterface;
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
        /** @var PlayerInterface[]|MockObject[] $players */
        $players = [
            $this->createMock(PlayerInterface::class),
            $this->createMock(PlayerInterface::class),
            $this->createMock(PlayerInterface::class),
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

        /** @var PlayerInterface $player */
        foreach ($collection->getElements() as $index => $player) {
            $this->assertSame($expectedOrders[$index], $player->getHandCount());
        }
    }
}
