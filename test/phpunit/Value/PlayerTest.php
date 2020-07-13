<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

use Arp\DominoGame\Value\Domino;
use Arp\DominoGame\Value\Player;
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

        $highestDouble = new Domino(6, 6);

        $player->addToHand(new Domino(1, 2));
        $player->addToHand(new Domino(3, 3));
        $player->addToHand(new Domino(1, 1));
        $player->addToHand($highestDouble);
        $player->addToHand(new Domino(1, 2));
        $player->addToHand(new Domino(4, 2));
        $player->addToHand(new Domino(5, 3));
        $player->addToHand(new Domino(1, 2));

        $result = $player->getHighestDouble();

        $this->assertSame($highestDouble, $result);
    }
}
