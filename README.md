[![Build Status](https://travis-ci.com/alex-patterson-webdev/domino-game.svg?branch=master)](https://travis-ci.com/alex-patterson-webdev/domino-game)


# Arp\DominoGame

## About

An object oriented solution to the Domino scoring challenge from Resume Library.

## Installation

Installation via [composer](https://getcomposer.org).

    require alex-patterson-webdev/domino-game ^1
    
Installation via GitHub

    git clone https://github.com/alex-patterson-webdev/domino-game.git
    
## Usage

To play the Domino Game execute the `index.php` file from the command line; providing a comma separated list 
of players who will playing. A minimum of two and a maximum of four players names must be provided.

For example, to play with 3 players, `Fred`, `Bob` and `Alice`, use the following command.

    php public/index.php Fred,Bob,Alice
    
The output will list, step by step, the players turns that are performed and the final winner.

## Unit Tests

PHP Unit test using [PHPUnit](https://github.com/sebastianbergmann/phpunit). Execute the tests by running the following command.

    php vendor/bin/phpunit
