<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

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
}
