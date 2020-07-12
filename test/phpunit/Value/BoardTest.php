<?php

declare(strict_types=1);

namespace ArpTest\Value\PlayerTest;

use Arp\DominoGame\Value\Board;
use Arp\DominoGame\Value\Domino;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\Value\PlayerTest
 */
class BoardTest extends TestCase
{
    /**
     * Assert that the board will be created with an empty collection of placed pieces.
     *
     * @covers \Arp\DominoGame\Value\Board::__construct
     */
    public function testBoardStartsWithZeroPlacedPieces(): void
    {
        $board = new Board();

        $this->assertSame(0, $board->getPlaced()->count());
    }

    /**
     * Assert that when placing the first piece then the provided domino is added to the start of the collection.
     *
     * @covers \Arp\DominoGame\Value\Board::place
     */
    public function testFirstPieceIsPlacedAtBeginningOfCollection(): void
    {
        $board = new Board();

        /** @var Domino|MockObject $domino */
        $domino = $this->createMock(Domino::class);

        $this->assertTrue($board->place($domino));
        $this->assertSame($domino, $board->getPlaced()->first());
    }

    /**
     * Assert that when calling getLeft() or getRight() after placing our first piece then the expected
     * placed domino is returned.
     *
     * @covers \Arp\DominoGame\Value\Board::place
     * @covers \Arp\DominoGame\Value\Board::getRight
     * @covers \Arp\DominoGame\Value\Board::getLeft
     */
    public function testGetLeftAndGetRightValuesAreCorrectForFirstPlacedPiece(): void
    {
        $board = new Board();

        $top = 3;
        $bottom = 5;

        /** @var Domino|MockObject $domino */
        $domino = $this->createMock(Domino::class);

        $domino->expects($this->once())
            ->method('getTopTile')
            ->willReturn($top);

        $domino->expects($this->once())
            ->method('getBottomTile')
            ->willReturn($bottom);

        $this->assertTrue($board->place($domino));

        $this->assertSame($domino, $board->getLeft());
        $this->assertSame($domino, $board->getRight());
    }
}
