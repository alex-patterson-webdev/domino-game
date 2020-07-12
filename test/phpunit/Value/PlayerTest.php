<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

use Arp\DominoGame\Value\Domino;
use Arp\DominoGame\Value\Player;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Value\PlayerTest
 */
final class PlayerTest extends TestCase
{
    /**
     * Assert that the player starts with an empty hand.
     *
     * @covers \Arp\DominoGame\Value\Player::getHand
     */
    public function testPlayerStartsWithAnEmptyHand(): void
    {
        $player = new Player('Bob');

        $this->assertSame(0, $player->getHand()->count());
    }

    /**
     * Assert that the highest numbered double will be returned from the call to getHighestDouble()
     *
     * @covers \Arp\DominoGame\Value\Player::getHighestDouble
     */
    public function testGetHighestDoubleWillReturnHighestDouble(): void
    {
        $player = new Player('Fred');

        $highestDouble = $this->createDominoMock(6, 6);

        $player->addToHand($this->createDominoMock(1, 2));
        $player->addToHand($this->createDominoMock(3, 3));
        $player->addToHand($this->createDominoMock(1, 1));
        $player->addToHand($highestDouble);
        $player->addToHand($this->createDominoMock(1, 2));
        $player->addToHand($this->createDominoMock(4, 2));
        $player->addToHand($this->createDominoMock(5, 3));
        $player->addToHand($this->createDominoMock(1, 2));

        $result = $player->getHighestDouble();

        $this->assertSame($highestDouble, $result);
    }

    /**
     * Create a new mocked Domino instance.
     *
     * @param int $top
     * @param int $bottom
     *
     * @return Domino|MockObject
     */
    private function createDominoMock(int $top, int $bottom): Domino
    {
        return new Domino($top, $bottom);

        /** @var Domino|MockObject $domino */
//        $domino = $this->createMock(Domino::class);
//
//        $domino->method('getTopTile')->willReturn($top);
//        $domino->method('getBottomTile')->willReturn($bottom);
//        $domino->method('isDouble')->willReturn($top === $bottom);

        return $domino;
    }
}
