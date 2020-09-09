# baseweb
A basic barebones PHP webproject running on
* PHP 7.2
* MySQL 5.2
        
Composer packages required:
* Phinx
* PHP-Cs-Fixer
* Simple-PHP-Router
* VLUCAS's PHPDOTENV

Before running the project:
* Install PHP 7.0 or higher
* Install MySQL 5.2 or higher
* Set PHP installation folder and \AppData\Roaming\Composer\vendor\bin on your OS path
* or ignore all that and just install XAMP
* Install Composer 

Configuring the project:
* Install the project dependencies using composer with the command:
* ```composer install```
* After that, using the command line, navigate to the main folder of the project and:
* ```Copy .env.dist to .env locally```
* ```Configure the .env file with DB connection info```
* ```Copy .phinx.yml.dist to phinx.yml locally```
* ```Configure the phinx.yml file with DB connection info```
* ```Run vendor/bin/phinx migrate```