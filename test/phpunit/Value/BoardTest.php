<?php

declare(strict_types=1);

namespace ArpTest\DominoGame\Value;

use Arp\DominoGame\Exception\DominoGameException;
use Arp\DominoGame\Exception\InvalidArgumentException;
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

        $board->place($domino);

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

        $board->place($domino);

        $this->assertSame($domino, $board->getLeft());
        $this->assertSame($domino, $board->getRight());

        $this->assertSame(5, $board->getLeftTile());
        $this->assertSame(5, $board->getRightTile());
    }

    /**
     * Assert that if we attempt to place() a domino to the board that doesn't match an exposed tile then a new
     * DominoGameException is thrown.
     *
     * @covers \Arp\DominoGame\Value\Board::place
     *
     * @throws DominoGameException
     */
    public function testPlacementOfNonMatchingPieceThrowsInvalidArgumentException(): void
    {
        $board = new Board();

        $firstDomino = $this->createDominoMock(1, 1);

        $board->place($firstDomino);

        $nonMatchDomino = $this->createDominoMock(2, 2);

        $nonMatchDomino->expects($this->once())
            ->method('getName')
            ->willReturn('2-2');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The domino \'%s\' cannot be placed on the board; tiles must match values \'%d\' or \'%d\'',
                '2-2',
                1,
                1
            )
        );

        $board->place($nonMatchDomino);
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
        $firstDomino = new Domino($start, $start);
        $board->place($firstDomino);

        // We expect to add this to the left of the placed dominoes...
        $leftPlaceDomino = new Domino($top, $bottom);
        $board->place($leftPlaceDomino);

        $leftTile = $board->getLeftTile();
        if ($top === $start) {
            $this->assertSame($leftTile, $bottom);
        } elseif ($bottom === $start) {
            $this->assertSame($leftTile, $top);
        } else {
            $this->fail(sprintf('The $start value \'%d\' should match either $top or $bottom', $start));
        }

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
     * Assert that we can place matching tiles to the 'right' of the collection.
     *
     * @covers \Arp\DominoGame\Value\Board::place
     * @covers \Arp\DominoGame\Value\Board::placeRight
     *
     * @throws InvalidArgumentException
     */
    public function testPlaceRightTileWithNonEmptyDeck(): void
    {
        $board = new Board();

        /**
         * @var Domino $domino1
         * @var Domino $domino2
         */
        $domino1 = new Domino(2, 2);
        $domino2 = new Domino(2, 3);
        $domino3 = new Domino(2, 5);

        $board->place($domino1);
        $board->place($domino2);
        $board->place($domino3);

        $rightDomino = new Domino(1, 5);

        $board->place($rightDomino);

        $this->assertSame(3, $board->getLeftTile());
        $this->assertSame(1, $board->getRightTile());
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
