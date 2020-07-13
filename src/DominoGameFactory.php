<?php

declare(strict_types=1);

namespace Arp\DominoGame;

use Arp\DominoGame\Exception\DominoGameException;
use Arp\DominoGame\Value\Player;
use Arp\DominoGame\Value\PlayerCollection;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\PsrLoggerAdapter;
use Laminas\Log\Writer\Stream;
use Psr\Log\LoggerInterface;

class DominoGameFactory
{
    /**
     * Create a new domino game with the provided player names.
     *
     * @todo Move to factory class
     *
     * @param array                $playerNames
     * @param int                  $maxTileSize
     * @param LoggerInterface|null $logger
     *
     * @return DominoGame
     * @throws DominoGameException
     */
    public function create(array $playerNames, int $maxTileSize, LoggerInterface $logger = null): DominoGame
    {
        $players = [];
        foreach ($playerNames as $playerName) {
            // @todo Sanitisation $playerName?
            $players[] = new Player($playerName);
        }

        $logger = $logger ?? $this->createDefaultLogger();

        return new DominoGame(new PlayerCollection($players), $logger, $maxTileSize);
    }

    /**
     * Create a simple default logger that will render the actions to stdout.
     *
     * @return LoggerInterface
     */
    private function createDefaultLogger(): LoggerInterface
    {
        $logger = new Logger();

        $writer = new Stream('php://stdout');
        $writer->setFormatter(new Simple('%message%'));
        $logger->addWriter($writer);

        return new PsrLoggerAdapter($logger);
    }
}
