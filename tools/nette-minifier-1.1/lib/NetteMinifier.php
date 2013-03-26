<?php

/**
 * Nette & PHP Minifier
 *
 * Inspired by David Grudl's PHP shrinker (http://latrine.dgx.cz/jak-zredukovat-php-skripty)
 * Copyright (c) 2009 Lukáš Doležal @ GDMT (dolezal@gdmt.cz)
 * Modified by Michal Koutný <xm.koutny@gmail.com> at 2011 for Nette 2.0.
 *
 * This source file is subject to the "General Public Licenee" (GPL)
 *
 * @copyright  Copyright (c) 2009 Lukáš Doležal (dolezal@gdmt.cz)
 * @license    http://www.gnu.org/copyleft/gpl.html  General Public License
 * @link       http://nettephp.com/cs/extras/nette-minifier
 * @package    Nette Minifier
 */
// PHP 4 & 5 compatibility
if (!defined('T_DOC_COMMENT'))
    define('T_DOC_COMMENT', -1);

if (!defined('T_ML_COMMENT'))
    define('T_ML_COMMENT', -1);

// PHP <5.3 compatibility
if (!defined('T_USE'))
    define('T_USE', -1);

if (!defined('T_NAMESPACE'))
    define('T_NAMESPACE', -1);

if (!defined('T_NS_SEPARATOR'))
    define('T_NS_SEPARATOR', -1);

class NetteMinifier {
    const NETTE_53 = 1;

    const NETTE_52 = 2;

    const NETTE_PREFIXED = 3;

    /**
     * Set of characters after that must not be space
     * @var <type>
     */
    public static $set = '!"#$&\'()*+,-./:;<=>?@[\]^`{|}';
    private static $setArray = array();
    private static $tagArray = array();
    protected $parsedFiles = array();
    // list from NetteLoader
    protected $classesList = array();
    // list from NetteLoader
    protected $netteDir = '.';

    public function __construct() {
        if (!function_exists('token_get_all'))
            throw new Exception('PHP tokenizer module is not present.');

        self::$setArray = array_flip(preg_split('//', self::$set));
        self::$tagArray = array(
            'abstract ',
            'access',
            'author',
            'category',
            'copyright',
            'deprecated',
            'description',
            'example',
            'final',
            'filesource',
            'global',
            'ignore',
            'internal',
            'license',
            'link',
            'method',
            'name',
            'package',
            'param',
            'property',
            'property-write',
            'property-read',
            'return',
            'see',
            'since',
            'static',
            'staticvar',
            'subpackage',
            'todo',
            'throws',
            'tutorial',
            'uses',
            'var',
            'version',);
    }

    public function getParsedFiles() {
        return $this->parsedFiles;
    }

    protected function isAlreadyParsed($file) {
        $file = realpath($file);
        return array_search($file, $this->parsedFiles) !== false;
    }

    private $debuging = FALSE;

    public function toggleDebug($enabled = TRUE) {
        $this->debuging = (bool) $enabled;
    }

    public function debugOut($out, $offset = 0) {
        if ($this->debuging)
            echo str_repeat(' ', $offset) . $out;
    }

    protected function minifyFile($file, $encloseWithPHPTags = FALSE, $ignoreNamespaces = FALSE, $nestingLevel = 0, $loaderFile = FALSE) {
        $this->debugOut("Parsing $file...\n", $nestingLevel);
        $file = realpath($file);
        if ($file === false) {
            $this->debugOut("^ File not found\n", $nestingLevel);
            return FALSE;
        }

        if ($this->isAlreadyParsed($file)) {
            $this->debugOut("^ File is already parsed\n", $nestingLevel);
            return FALSE;
        }

        $this->parsedFiles[] = $file;

        $space = $output = $preoutput = $namespace = '';
        $requireFollow = $requireHere = $wasFirstOpenTag = false;
        $parsingName = NULL;
        $uses = array();

        if ($this->debuging)
            $output = "/*@$file*/";

        // walk over all tokens in file
        $tokens = token_get_all(file_get_contents($file));
        $current_token_index = 0;
        while ($current_token_index < count($tokens)) {
            // normalize token for use below
            $token = $tokens[$current_token_index++];
            if (!is_array($token))
                $token = array(0, $token);

            if ($loaderFile && $token[0] == T_REQUIRE_ONCE) {
                break;
            }


            //************ was required file ************//

            if ($requireFollow) {
                // until token is not string with file name
                if ($token[0] == T_CONSTANT_ENCAPSED_STRING) {
                    // assume that before every file name is __DIR__
                    $requireFile = realpath(dirname($file) . trim($token[1], "'"));
                    $this->debugOut("Going into:\n", $nestingLevel);

                    // minificate and prepend required file to future output
                    // prepending is important due to namespaces
                    $_m = $this->minifyFile($requireFile, FALSE, $requireHere || $ignoreNamespaces, $nestingLevel + 1);
                    if ($_m !== FALSE) {
                        if ($requireHere)
                            $output .= $_m . '<?php '; // this is little bet to that required files ends outside php
                        else
                            $preoutput .= $_m;
                    }
                    unset($requireFile, $_m);
                }

                // wait until 'require' command do not end
                if ($token[1] == ';') {
                    $requireFollow = $requireHere = false;
                    continue; // next token
                }

                continue; // next token
            }


            //************ was namespace/use declared ************//

            if ($parsingName !== NULL) {
                switch ($parsingName) {
                    case T_NAMESPACE:
                        $into = &$namespace;
                        break;
                    case T_USE:
                        $into = &$uses[key($uses)];
                        break;
                }
                $skip = true;
                switch ($token[0]) {
                    case 0:
                        if ($token[1] == ';') {
                            $parsingName = NULL;
                            unset($into);
                        }
                        if ($token[1] == ',') {
                            $uses[] = '';
                            end($uses);
                        }
                        if ($token[1] == '(') { // use in closure declaration
                            $parsingName = NULL;
                            array_pop($uses);
                            unset($into);
                            $output .= 'use';
                            $skip = false;
                        }
                        break;
                    case T_NS_SEPARATOR:
                    case T_STRING:
                        $into .= $token[1];
                        break;
                    case T_AS:
                        $into .= ' ' . $token[1] . ' ';
                }
                if ($skip)
                    continue; // next token
            }

            //************ parent class/interface requirement ************//
            if ($token[0] == T_EXTENDS || $token[0] == T_IMPLEMENTS) {
                // search T_STRING which is name of class/interface
                $i = $current_token_index;

                while (true) {
                    $class_name = '';
                    while (true) {
                        if (is_array($tokens[$i]) && $tokens[$i][0] == T_STRING) {
                            $class_name .= strtolower($tokens[$i][1]);
                            if ($tokens[$i + 1][0] != T_NS_SEPARATOR)
                                break;
                        }
                        else if (is_array($tokens[$i]) && $tokens[$i][0] == T_NS_SEPARATOR)
                            $class_name .= '\\';


                        $i++;
                    }

                    // search file with this class/interface

                    $this->debugOut("Found extends/implements $class_name \n", $nestingLevel);
                    foreach ($this->classesList as $class => $class_file) {
                        if ($class == $class_name || $class == strtolower($namespace) . '\\' . $class_name) {
                            $this->debugOut("Find file $class_file\n", $nestingLevel);
                            $_m = $this->minifyFile($this->netteDir . $class_file, FALSE, $ignoreNamespaces, $nestingLevel + 1);
                            if ($_m !== FALSE) {
                                $preoutput .= $_m;
                            }
                            break;
                        }
                    }

                    if ($tokens[$i + 1] == ',') {
                        $i++;
                        continue;
                    }
                    break;
                } // main loop
                unset($i, $class_name, $_m, $class_file);
            }

            //************ regular token recognition ************//

            switch ($token[0]) {
                case T_COMMENT:
                case T_ML_COMMENT:
                case T_WHITESPACE:
                    $space = ' ';
                    continue 2; // next token
                case T_DOC_COMMENT:
                    if (!$this->keepAnnotation($token[1])) {
                        continue 2;
                    }
                    break;
                case T_REQUIRE:
                    // In nette is some places where is included HTML templates.
                    // These places are only places where is require command.
                    $requireHere = true;
                case T_REQUIRE_ONCE:
                    // parse required file name in next tokens and go into it
                    $requireFollow = true;
                    continue 2; // next token
                case T_OPEN_TAG:
                    // if it is first open tag in parsed file
                    if (!$wasFirstOpenTag) {
                        $wasFirstOpenTag = true;
                        continue 2; // next token
                    }
                    // else print it into output (may be it is followed by non-PHP text)
                    break;
                case T_NAMESPACE:
                    // parse namespace definition in next tokens
                    $parsingName = T_NAMESPACE;
                    $namespace = '';
                    continue 2; // next token
                case T_USE:
                    // parse use definition in next tokens
                    $parsingName = T_USE;
                    $uses[] = '';
                    end($uses);
                    continue 2; // next token
                case T_CLASS:
                case T_INTERFACE:
                case T_STRING:
                case T_FUNCTION:
                    $space = "\n";
                    break;
            }
            if (isset(self::$setArray[substr($output, -1)]) || isset(self::$setArray[$token[1]{0}]))
                $space = '';

            $output .= $space . $token[1];
            $space = '';
        } // token walk
        //************ final phase ************//

        if ($preoutput != '' && !isset(self::$setArray[substr($preoutput, -1)]))
            $preoutput .= "\n";

        $heading = $preoutput;

        $footer = '';

        if (PHP_VERSION >= '5.3.0' && !$ignoreNamespaces) {
            $heading .= "namespace $namespace{";

            // print merged namespace uses
            if (count($uses) > 0) {
                $heading .= 'use ' . implode(',', array_unique($uses)) . ';';
            }

            $footer = '}';
        }

        if ($heading != '' && !isset(self::$setArray[substr($heading, -1)]))
            $heading .= "\n";

        $output = $heading . $output . $footer;

        if ($encloseWithPHPTags)
            $output = "<?php\n$output";

        $this->debugOut("Done $file\n", $nestingLevel);

        return $output;
    }

    protected function getNettePHPVersion($netteDir) {
        $loaderFile = $netteDir . '/Loaders/NetteLoader.php';
        if (!file_exists($loaderFile))
            throw new InvalidArgumentException('Can not find NetteLoader class file in directory \'' . $netteDir . '\'');

        require_once $loaderFile;

        //PHP 5.3 version
        if (class_exists('\\Nette\\Loaders\\NetteLoader')) {
            $this->debugOut("Detected Nette for PHP 5.3\n");
            return self::NETTE_53;
        }
        //PHP 5.2 verions
        else if (class_exists('NetteLoader')) {
            $this->debugOut("Detected Nette for PHP 5.2\n");
            return self::NETTE_52;
        } else if (class_exists('NNetteLoader')) {
            $this->debugOut("Detected Nette for PHP 5.2 w/ class prefixes\n");
            return self::NETTE_PREFIXED;
        } else {
            $this->debugOut("Detection of Nette variant fault\n");
            return NULL;
        }
    }

    protected function getNetteFiles($netteDir) {
        $loaderFile = $netteDir . '/Loaders/NetteLoader.php';
        if (!file_exists($loaderFile))
            throw new InvalidArgumentException('Can not find NetteLoader class file in directory \'' . $netteDir . '\'');

        require_once $loaderFile;

        //PHP 5.3 version
        if (class_exists('\\Nette\\Loaders\\NetteLoader'))
            $loaderClass = '\\Nette\\Loaders\\NetteLoader';
        //PHP 5.2 verions
        else if (class_exists('NetteLoader'))
            $loaderClass = 'NetteLoader';
        else if (class_exists('NNetteLoader'))
            $loaderClass = 'NNetteLoader';
        else
            throw new Exception('Class NetteLoader not found.');

        $this->parsedFiles[] = realpath($loaderFile); // NetteLoader is not longer needed in minified version

        $netteLoader = new $loaderClass();
        return $netteLoader->list;
    }

    public function minifyNette($netteDir = NULL) {
        $netteDir = realpath(is_string($netteDir) ? $netteDir : './Nette/');
        if (!is_dir($netteDir))
            throw new InvalidArgumentException('Can not find Nette Framework directory.');

        $this->debugOut("Nette directory found $netteDir\n");

        $this->parsedFiles = array();

        require_once $netteDir . '/loader.php'; //load all required "loader" files

        $netteVersion = $this->getNettePHPVersion($netteDir);
        $this->classesList = $this->getNetteFiles($netteDir);
        $this->netteDir = $netteDir;

        $minified = "<?php //netteloader=Nette\Framework\n"; // comment is for RobotLoader
        // minify loader.php logic
        if (($_m = $this->minifyFile($netteDir . '/loader.php', FALSE, $netteVersion != self::NETTE_53, 0, TRUE)) !== FALSE)
            $minified .= $_m;
        // minify Nette
        foreach ($this->classesList as $filename) {
            if (($_m = $this->minifyFile($netteDir . $filename, FALSE, $netteVersion != self::NETTE_53)) !== FALSE)
                $minified .= $_m;
        }

        // append executive code
        $minified .= $this->getLoaderCode();


        return $minified;
    }

    /**
     *
     * @param type $docComment
     * @return type 
     * @
     */
    protected function keepAnnotation($docComment) {
        $cr = new ReflectionClass('\\Nette\\Reflection\\AnnotationsParser');
        $mr = $cr->getMethod('parseComment');
        $mr->setAccessible(true); // as ugly as semantic information in comments
        $data = $mr->invoke(null, $docComment);
        return count(array_diff(array_keys($data), self::$tagArray)) > 0;
    }

    protected function getLoaderCode() {
        return 'namespace{Nette\Diagnostics\Debugger::_init();Nette\Utils\SafeStream::register();
function callback($callback, $m = NULL){
	return ($m === NULL && $callback instanceof Nette\Callback) ? $callback : new Nette\Callback($callback, $m);}
function dump($var){
	foreach (func_get_args() as $arg) Nette\Diagnostics\Debugger::dump($arg);return $var;}}';
    }

}

