<?php

include 'vendor/autoload.php';

use Arp\DominoGame\DominoGameFactory;
use Arp\DominoGame\Exception\DominoGameException;

$playerNames = isset($argv[1]) ? explode(',', $argv[1]) : null;
if (empty($playerNames)) {
    echo 'Please provide at least two player names as a comma separated list' . PHP_EOL;
    return 0;
}

$playerNameCount = count($playerNames);
if ($playerNameCount < 1 || $playerNameCount > 4) {
    echo 'Please provide between 1-4 player names' . PHP_EOL;
    return 0;
}

$maxTileSize = 6;
$handSize = 7;

try {
    $game = (new DominoGameFactory())->create($playerNames, $maxTileSize);
    $game->run($handSize);
} catch (DominoGameException $e) {
    echo 'The game could not be executed: ' . $e->getMessage() . PHP_EOL;
    return 1;
}
