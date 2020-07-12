<?php

declare(strict_types=1);

namespace ArpTest\Value\PlayerTest;

use Arp\DominoGame\Exception\DominoGameException;
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
     * Assert that when placing a new domino to an empty collection that is not a 'double' a new
     * DominoGameException will be thrown.
     *
     * @throws DominoGameException
     */
    public function testPlaceWillThrowDominoGameExceptionIfFirstPieceIsNotADouble(): void
    {
        $board = new Board();

        $domino = $this->createDominoMock(1, 2);
        $dominoName = '1-2';

        $domino->expects($this->once())
            ->method('getName')
            ->willReturn($dominoName);

        $this->expectException(DominoGameException::class);
        $this->expectExceptionMessage(
            sprintf('The first domino place must be a double \'%s\' provided', $dominoName)
        );

        $board->place($domino);
    }


    /**
     * Assert that when placing the first piece then the provided domino is added to the start of the collection.
     *
     * @covers \Arp\DominoGame\Value\Board::place
     *
     * @throws DominoGameException
     */
    public function testFirstPieceIsPlacedAtBeginningOfCollection(): void
    {
        $board = new Board();

        /** @var Domino|MockObject $domino */
        $domino = $this->createDominoMock(5, 5);

        $this->assertTrue($board->place($domino));
        $this->assertSame($domino, $board->getPlaced()->first());
    }

    /**
     * Assert that when calling getLeft() or getRight() after placing our first piece then the expected
     * placed domino is returned.
     *
     * @covers \Arp\DominoGame\Value\Board::place
     * @covers \Arp\DominoGame\Value\Board::getRight
     * @covers \Arp\DominoGame\Value\Board::getRightTile
     * @covers \Arp\DominoGame\Value\Board::getLeft
     * @covers \Arp\DominoGame\Value\Board::getLeftTile
     *
     * @throws DominoGameException
     */
    public function testGetLeftAndGetRightValuesAreCorrectForFirstPlacedPiece(): void
    {
        $board = new Board();

        /** @var Domino|MockObject $domino */
        $domino = $this->createDominoMock(5, 5);

        $this->assertTrue($board->place($domino));

        $this->assertSame($domino, $board->getLeft());
        $this->assertSame($domino, $board->getRight());

        $this->assertSame(5, $board->getLeftTile());
        $this->assertSame(5, $board->getRightTile());
    }

    /**
     * Assert that if we attempt to place a domino to the board that doesn't match an exposed tile then FALSE
     * is returned from place().
     *
     * @covers \Arp\DominoGame\Value\Board::place
     *
     * @throws DominoGameException
     */
    public function testPlacementOfNonMatchingPieceReturnsFalse(): void
    {
        $board = new Board();

        $firstDomino = $this->createDominoMock(1, 1);

        $this->assertTrue($board->place($firstDomino));

        $nonMatchDomino = $this->createDominoMock(2, 2);

        $this->assertFalse($board->place($nonMatchDomino));
    }

    /**
     * Assert that when we call place() on a non-empty board we expect the provided domino to be added to the top/left.
     *
     * @param int $start  The starting double that should be placed.
     * @param int $top    The value of the top tile to place.
     * @param int $bottom The value of the bottom tile to place.
     *
     * @dataProvider getLeftPlacedDominoIsAddedToTheBeginningOfANonEmptyDominoCollectionData
     *
     * @covers       \Arp\DominoGame\Value\Board::place
     * @covers       \Arp\DominoGame\Value\Board::placeLeft
     *
     * @throws DominoGameException
     */
    public function testLeftPlacedDominoIsAddedToTheBeginningOfANonEmptyDominoCollection(
        int $start,
        int $top,
        int $bottom
    ): void {
        $board = new Board();

        // We need to first make the collection non-empty
        $firstDomino = $this->createDominoMock($start, $start);
        $this->assertTrue($board->place($firstDomino));

        // We expect to add this to the left of the placed dominoes...
        $leftPlaceDomino = $this->createDominoMock($top, $bottom);

        $placeResult = $board->place($leftPlaceDomino);

        $leftTile = $board->getLeftTile();
        if ($top === $start) {
            $this->assertSame($leftTile, $bottom);
        } elseif ($bottom === $start) {
            $this->assertSame($leftTile, $top);
        } else {
            $this->fail(sprintf('The $start value \'%d\' should match either $top or $bottom', $start));
        }

        $this->assertTrue($placeResult);
        $this->assertSame($board->getLeft(), $leftPlaceDomino);
    }

    /**
     * @return array
     */
    public function getLeftPlacedDominoIsAddedToTheBeginningOfANonEmptyDominoCollectionData(): array
    {
        return [
            [5, 5, 3],
            [5, 3, 5],

            [1, 1, 3],
            [1, 3, 1],

            [0, 0, 3],
            [0, 3, 0],
        ];
    }

    /**
     * Create a mock domino instance that will return the provided tile values.
     *
     * @param int $top
     * @param int $bottom
     *
     * @return Domino|MockObject
     */
    private function createDominoMock(int $top, int $bottom): Domino
    {
        /** @var Domino|MockObject $domino */
        $domino = $this->createMock(Domino::class);

        $domino->method('getTopTile')->willReturn($top);
        $domino->method('getBottomTile')->willReturn($bottom);
        $domino->method('isDouble')->willReturn($top === $bottom);

        return $domino;
    }
}
