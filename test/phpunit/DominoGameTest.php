<?php

declare(strict_types=1);

namespace ArpTest\DominoGame;

use Arp\DominoGame\DominoGame;
use Arp\DominoGame\Exception\DominoGameException;
use Arp\DominoGame\Value\DominoCollection;
use Arp\DominoGame\Value\Player;
use Arp\DominoGame\Value\PlayerCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\DominoGame
 */
final class DominoGameTest extends TestCase
{
    /**
     * @var PlayerCollection|MockObject
     */
    private $players;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->players = $this->createMock(PlayerCollection::class);

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * Assert that if we provide a collection with an invalid number of players to the DominoGame class that a
     * DominoGameException will be thrown with the correct exception message.
     *
     * @param int $playerCount
     *
     * @covers       \Arp\DominoGame\DominoGame::__construct
     * @covers       \Arp\DominoGame\DominoGame::reset
     *
     * @dataProvider getInvalidPlayerCountWillThrowDominoGameExceptionData
     *
     * @throws DominoGameException
     */
    public function testInvalidPlayerCountWillThrowDominoGameException(int $playerCount): void
    {
        $this->players->expects($this->once())
            ->method('count')
            ->willReturn($playerCount);

        $this->expectException(DominoGameException::class);
        $this->expectExceptionMessage(
            sprintf('There must be a minimum of 1 and a maximum of 4 players; %d provided', $playerCount)
        );

        new DominoGame($this->players, $this->logger, 6);
    }

    /**
     * @return array
     */
    public function getInvalidPlayerCountWillThrowDominoGameExceptionData(): array
    {
        return [
            [0],
            [1],
            [5],
            [-12],
            [1000],
        ];
    }

    /**
     * Assert that the internal collection of dominoes (the deck) is correctly reset and a new one configured
     * when calling reset().
     *
     * @param int $expectedCount
     * @param int $maxTileSize
     *
     * @covers       \Arp\DominoGame\DominoGame::__construct
     * @covers       \Arp\DominoGame\DominoGame::reset
     * @covers       \Arp\DominoGame\DominoGame::createDominoCollection
     *
     * @dataProvider getPopulationOfNewBoneyardCollectionWithTheCorrectNumberOfDominoesData
     *
     * @throws DominoGameException
     */
    public function testPopulationOfNewDeckCollectionWithTheCorrectNumberOfDominoes(
        int $expectedCount,
        int $maxTileSize
    ): void {
        $players = $this->createMockPlayersArray(2);
        $playersCollection = $this->createMockPlayersCollection($players);

        foreach ($players as $player) {
            /** @var DominoCollection|MockObject $dominoCollection */
            $dominoCollection = $this->createMock(DominoCollection::class);

            $player->expects($this->once())
                ->method('getHand')
                ->willReturn($dominoCollection);

            $dominoCollection->expects($this->once())
                ->method('removeElements')
                ->with(null);
        }

        $game = new DominoGame($playersCollection, $this->logger, $maxTileSize);

        $this->assertSame($expectedCount, $game->getDeck()->count());
    }

    /**
     * @return array
     */
    public function getPopulationOfNewBoneyardCollectionWithTheCorrectNumberOfDominoesData(): array
    {
        return [
            [28, 6],
        ];
    }

    /**
     * Assert that a $handSize less than 1 will result in a DominoGameException being thrown when calling deal().
     *
     * @param int $handSize The hand size that should be tested.
     *
     * @covers \Arp\DominoGame\DominoGame::deal
     * @dataProvider getDealWillThrowDominoGameExceptionIfProvidedAHandSizeLessThanOneData
     *
     * @throws DominoGameException
     */
    public function testDealWillThrowDominoGameExceptionIfProvidedAHandSizeLessThanOne(int $handSize): void
    {
        $players = $this->createMockPlayersArray(2);
        $playersCollection = $this->createMockPlayersCollection($players);

         $game = new DominoGame($playersCollection, $this->logger, 6);

         $this->expectException(DominoGameException::class);
         $this->expectExceptionMessage('Hand sizes must be a minimum of 1');

         $game->deal($handSize);
    }

    /**
     * @return array
     */
    public function getDealWillThrowDominoGameExceptionIfProvidedAHandSizeLessThanOneData(): array
    {
        return [
            [0],
            [-1],
            [-100],
            [-1212],
        ];
    }

    /**
     * Assert that the deal method will throw a DominoGameException if the provided $handSize exceeds the maximum
     * number that is available
     *
     * @param int $handSize The invalid hand size to test
     *
     * @dataProvider getDealWillThrowDominoGameExceptionIfProvidedAHandSizeExceedsAvailableDeckSizeData
     *
     * @throws DominoGameException
     */
    public function testDealWillThrowDominoGameExceptionIfProvidedAHandSizeExceedsAvailableDeckSize(int $handSize): void
    {
        $players = $this->createMockPlayersArray(2);
        $collection = $this->createMockPlayersCollection($players);

        $game = new DominoGame($collection, $this->logger, 6);

        $expectedDeckCount = 28;

        $this->expectException(DominoGameException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The hand size %d exceeds the maximum permissible for current deck size of %d',
                $handSize,
                $expectedDeckCount
            )
        );

        $game->deal($handSize);
    }

    /**
     * @return array
     */
    public function getDealWillThrowDominoGameExceptionIfProvidedAHandSizeExceedsAvailableDeckSizeData(): array
    {
        return [
            [28],
            [100],
        ];
    }

    /**
     * Assert that when providing a varied $playerCount we are able to correct deal the number of expected
     * dominoes to each one
     *
     * @covers       \Arp\DominoGame\DominoGame::deal
     *
     * @param int $playerCount The number of players to test
     *
     * @dataProvider getDealingProvidesTheCorrectNumberOfDominoesToEachPlayerData
     *
     * @throws DominoGameException
     */
    public function testDealingProvidesTheCorrectNumberOfDominoesToEachPlayer(int $playerCount): void
    {
        $deckSize = 28;
        $handSize = 7;

        $players = [];
        for ($x = 0; $x < $playerCount; $x++) {
            $players[] = new Player('Player ' . $x);
        }

        $game = new DominoGame(new PlayerCollection($players), $this->logger, 6);

        $game->deal($handSize);

        $deckResult = $game->getDeck();
        $playersResult = $game->getPlayers();

        $expectedDeckCount = $deckSize - ($playerCount * $handSize);

        $this->assertSame($expectedDeckCount, $deckResult->count());

        /** @var Player $player */
        foreach ($playersResult as $player) {
            $this->assertSame(7, $player->getHandCount());
        }
    }

    /**
     * @return array
     */
    public function getDealingProvidesTheCorrectNumberOfDominoesToEachPlayerData(): array
    {
        return [
            [2],
            [3],
            [4],
        ];
    }

    /**
     * Create an array of player mock objects.
     *
     * @param int $numberOfPlayers
     *
     * @return Player[]|MockObject[]
     */
    private function createMockPlayersArray(int $numberOfPlayers): array
    {
        /** @var Player[]|MockObject $players */
        $players = [];
        for ($x = 0; $x < $numberOfPlayers; $x++) {
            $players[] = $this->createMock(Player::class);
        }
        return $players;
    }

    /**
     * Create a new player collection mock object with iterable players.
     *
     * @param array|iterable $players
     *
     * @return PlayerCollection|MockObject
     */
    private function createMockPlayersCollection(iterable $players = []): object
    {
        /** @var PlayerCollection|MockObject $collection */
        $collection = $this->createMock(PlayerCollection::class);

        $collection->method('getIterator')->willReturn(new \ArrayIterator($players));
        $collection->method('count')->willReturn(count($players));

        return $collection;
    }
}
