# PGO Manager
This is a PHP script to generate data files for front ended app for Pokemon GO's PVP battle system.

## About
This application run on pure PHP

This application also uses Composer to handle dependencies and namespacing. So, to run it you must have installed on your environment:

* PHP (7.4 or greater) (_https://www.php.net/manual/en/install.php_)
* Composer (_https://getcomposer.org/download/_)

## Setting up
After cloning the rep just run
`` composer install ``
to install dependencies

## Commands

### v1 commands (deprecated)
Creating quick moves database file

`php index.php jsquick`

Creating charge moves database file

`php index.php jscharge`

Creating full database file

`php index.php jsdb`

### v2 commands
`php index.php new_database`

Analyse and proccess content of Pokemon Go's game master file at [here](https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json) to generate the properly formmated database files for the front end application