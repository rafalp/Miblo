<?php

/*
 * This file is part of the Miblo package.
 *
 * (c) Rafał Pitoń <kontakt@rpiton.com>
 * 
 * $Release: 0012 $
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// CLI
(php_sapi_name() === "cli") or die('Not a CLI request');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'library' .
	DIRECTORY_SEPARATOR . 'Pirx' . DIRECTORY_SEPARATOR . 'Autoloader.php';

use Pirx\Autoloader;
use Miblo\Application;

new Autoloader();
new Application(__DIR__, array(
	'translation'	=> 'pl',
	
	'author'		=> 'DEMOn',
	'name'			=> 'Miblo Demoblog',
	'description'	=> 'A demonstration of Miblo capabilities',
	'domain'		=> 'demolabs.com',
	'path'			=> 'miblo',
));