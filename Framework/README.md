About
This framework was created to support the teaching of the subject Development of intranet and intranet applications (VAII) at the Faculty of Management Science and Informatics of University of Žilina. Framework demonstrates how the MVC architecture works.

Instructions and documentation
The framework source code is fully commented. In case you need additional information to understand, visit the WIKI stránky (only in Slovak).

Docker configuration
The Framework has a basic configuration for running and debugging web applications in the <root>/docker directory. All necessary services are set in docker-compose.yml file. After starting them, it creates the following services:

web server (Apache) with the PHP 8.3
MariaDB database server with a created database named according MYSQL_DATABASE environment variable
Adminer application for MariaDB administration
Other notes:
WWW document root is set to the public in the project directory.
The website is available at http://localhost/.
The server includes an extension for PHP code debugging Xdebug 3, uses the
port 9003 and works in "auto-start" mode.
PHP contains the PDO extension.
The database server is available locally on the port 3306. The default login details can be found in .env file.
Adminer is available at http://localhost:8080/