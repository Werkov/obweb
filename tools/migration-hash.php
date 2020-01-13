#!/usr/bin/php7.0
<?php
define('LIBS_DIR', '../libs');
define('APP_DIR', '../app');
use Nette\Diagnostics\Debugger;

require LIBS_DIR . '/Nette/loader.php';

// Enable Nette Debugger for error visualisation & logging
Debugger::$logDirectory = __DIR__ . '/../log';
Debugger::$strictMode = TRUE;
Debugger::enable();


// Configure application
$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');

// Enable RobotLoader - this will load all classes automatically
$configurator->createRobotLoader()
        ->addDirectory(APP_DIR)
        ->addDirectory(LIBS_DIR)
        ->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(APP_DIR . '/config/config.neon');
$container = $configurator->createContainer();

// Opens already started session
if ($container->session->exists()) {
    $container->session->start();
}

dibi::connect($container->parameters['database']);

echo "Migrační skript\n";
$dbname = "oob";
\dibi::query("USE %n", $dbname);

\dibi::begin();
//users
foreach(\dibi::query("SELECT id, password FROM system_user")->fetchAll() as $row) {
	\dibi::query("UPDATE system_user SET password = %s WHERE id = %i",
		password_hash($row['password'], PASSWORD_BCRYPT), $row['id']);
}
\dibi::commit();



echo "Done\n";
exit;
