<?php

/**
 * OOB bootstrap file.
 *
 * @author  michal
 */
use Nette\Diagnostics\Debugger;
use Nette\Application\Routers\Route;
use Nette\Environment;

// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/nette.min.php';

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
$configurator->addConfig(__DIR__ . '/config/config.neon');
$container = $configurator->createContainer();

// Opens already started session
//if ($container->session->exists()) {
//    $container->session->start();
//}


dibi::connect($container->parameters['database']);

$container->parameters['baseUrl'] = rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');

// Step 4: Setup application router
$domain = $container->parameters['domain'];
$router = $container->router;

// organized races at subdomains
$router[] = new Route("//<subdomain (?!www)[^/]+>.$domain/index.php", 'Organization:Homepage:default', Route::ONE_WAY);
$router[] = new Route("//<subdomain (?!www)[^/]+>.$domain/[<race>-]<presenter>[/<action>[/<id>]]", array(
            'presenter' => array(
                Route::VALUE => 'Organization:Homepage',
                Route::FILTER_TABLE => array(
                    'home' => 'Organization:Homepage',
                    'informace' => 'Organization:Info',
                    'vysledky' => 'Organization:Results',
                    'startovky' => 'Organization:Start',
                    'ostatni' => 'Organization:Other',
                ),
            ),
            'action' => array(
                Route::VALUE => 'default',
                Route::FILTER_TABLE => array(
                    'rozpis' => 'details',
                    'pokyny' => 'instructions',
                    'trate' => 'tracks',
                    'mezicasy' => 'splits',
                    'kategorie' => 'category',
                    'oddil' => 'club',
                ),
            ),
            'id' => null,
        ));
$router[] = new Route('index.php', 'Public:Homepage:default', Route::ONE_WAY);
$router[] = new Route("//[!www.]$domain/mcr11[/.*]", array(
            'presenter' => 'Public:Homepage',
            'action' => 'default',
            'q' => 'mcr11',
                ), Route::ONE_WAY);
$router[] = new Route("//[!www.]$domain/<presenter>[/<action>[/<id>]]", array(
            'presenter' => array(
                Route::VALUE => 'Public:Homepage',
                Route::FILTER_TABLE => array(
                    'poradame' => 'Public:Organization',
                    'prihlasky' => 'Application:Race',
                    'clenove' => 'Public:Member',
                    'fotografie' => 'Public:Gallery',
                    'dokumenty' => 'Public:Document',
                    'odkazy' => 'Public:Links',
                    'forum' => 'Public:Forum',
                    'ostatni' => 'Public:StaticPage',
                    'pub' => 'Public:Publication',
                    'auth' => 'Public:Authentication',
                ),
            ),
            'action' => array(
                Route::VALUE => 'default',
                Route::FILTER_TABLE => array(
                    'slozka' => 'directory',
                    'galerie' => 'gallery',
                    'clanek' => 'articleDetail',
                    'tema' => 'topic',
                    'vlakno' => 'thread',
                    'nove-vlakno' => 'newThread',
                    'profil' => 'profile',
                ),
            ),
            'id' => null,
        ));

// 4b) Register extra form controls
use Nette\Forms\Container;

Container::extensionMethod('\\Nette\\Forms\\Container::addWysiwyg', array('\\OOB\\WysiwygHelper', 'addWysiwyg'));
Container::extensionMethod('\\Nette\\Forms\\Container::addColorPicker', array('\\OOB\\ColorPickerHelper', 'addColorPicker'));

Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
            return $container[$name] = new OOB\DatePicker($label);
        });
Container::extensionMethod('addMultipleTextSelect', function (Container $container, $name, OOB\Forms\IItemsModel $model, $label = NULL) {
            return $container[$name] = new OOB\MultipleTextSelect($model, $label);
        });
MultipleFileUpload::register();
MultipleFileUpload::setQueuesModel(new MFUQueuesDibi());
PavelMaca\Captcha\CaptchaControl::register();
Nette\Forms\Controls\CheckboxList::register();
\OOB\TextUrl::register();


$conf = $container->parameters['gallery'];
LayoutHelpers::$thumbDirUri = $conf['thumbPath'];

//require_once APP_DIR . '/../migration.php';
// Configure and run application
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
$application->run();


