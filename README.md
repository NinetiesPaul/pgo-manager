#Simple web system for Pokemon PVP Team management

Project running on:
* ```PHP 7.2```
* ```MySQL 5.2```

Composer packages used:
* ```Phinx```
* ```PHP-Cs-Fixer```
* ```Simple-PHP-Router```
* ```VLUCAS's PHPDOTENV```

Before running the project:
* Install PHP 7.0 or higher
* Install MySQL 5.2 or higher
* Set PHP installation folder and \AppData\Roaming\Composer\vendor\bin on your OS path
* or ignore all that and just install XAMP
* Install Composer 

Configuring the project:
* Install the project dependencies using Composer (https://getcomposer.org/):
* ```Instalation depends on OS```
* After that, using the command line, navigate to the main folder of the project and:
* ```Copy .env.dist to .env locally```
* ```Configure the .env file with DB connection info```
* ```Copy phinx.yml.dist to phinx.yml locally```
* ```Configure the phinx.yml file with DB connection info```
* ```Run vendor/bin/phinx migrate```
* ```Copy includes/files/pokedb/_hash.json.dist to hash.json locally```
* ```Put CSV file with DPS information on includes/files with comprehensive_dps name```
* ```Access local address depending on your hosting configuration. The project is ready to use!```
