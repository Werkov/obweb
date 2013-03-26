<?php

/**
 * Nette & PHP Minifier
 *
 * Inspired by David Grudl's PHP shrinker (http://latrine.dgx.cz/jak-zredukovat-php-skripty)
 * Copyright (c) 2009 Lukáš Doležal @ GDMT (dolezal@gdmt.cz)
 *
 * This source file is subject to the "General Public Licenee" (GPL)
 *
 * @copyright  Copyright (c) 2009 Lukáš Doležal (dolezal@gdmt.cz)
 * @license    http://www.gnu.org/copyleft/gpl.html  General Public License
 * @link       http://nettephp.com/cs/extras/nette-minifier
 * @package    Nette Minifier
 */


require_once dirname(__FILE__) . '/lib/NetteMinifier.php';

$minifier = new NetteMinifier();

$minifier->toggleDebug(array_search('--debug', $argv) !== false && array_search('--stdout', $argv) === false);

$minified = $minifier->minifyNette(isset($argv[1]) ? $argv[1] : NULL);

if (array_search('--stdout', $argv) !== false)
	echo $minified;
else
{
	echo 'Parsed files: ' . count($minifier->getParsedFiles()) . "\n";
	
	$outfile = 'loader.minified.php';
	if (($i = array_search('--outfile', $argv)) !== false && isset($argv[$i+1]))
	{
		$outfile = $argv[$i+1];
	}
	
	fwrite(fopen($outfile, 'w'), $minified);
	echo "Minified version saved as $outfile\n";
}