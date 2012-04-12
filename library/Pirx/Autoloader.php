<?php

/*
 * This file is part of the Pirx package.
 *
 * (c) Rafał Pitoń <kontakt@rpiton.com>
 * 
 * $Release: 0706 $
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pirx;

class Autoloader
{
	/**
	 * Already initialised?
	 *
	 * @var bool
	 */
	private static $_initialised		= false;
	
	/**
	 * Libraries directory
	 *
	 * @var string
	 */
	private static $_libDirectory		= NULL;
	
	/**
	 * List of loaded classess
	 *
	 * @var array
	 */
	private static $_loadedClasses		= array();
	
	/**
	 * Logging cache loads enabled?
	 *
	 * @var bool
	 */
	private static $_logClasses			= false;
	
	/**
	 * Constructs Autoloader
	 * 
	 */	
	public function __construct()
	{
		// Initialising for first time?
		if (self::$_initialised)
		{
			// Throw Fatal exception
			throw new ExceptionFatal('Autoloader already initialised!', 6);
		}
		else
		{
			// Yes, allow initialisation
			self::$_initialised = true;
		}
		
		// Set libraries directory
		self::$_libDirectory = substr(__DIR__, 0, strrpos(__DIR__, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;

		// Register Autoloader
		spl_autoload_register(array($this, 'loadFile'));
	}
	
	/**
	 * Loaded classes list accessor
	 *
	 * @return array
	 */
	public static function getLoadedClasses()
	{
		return self::$_loadedClasses;
	}
	
	/**
	 * Autoloads file
	 *
	 * @param string $className
	 */
	public function loadFile($className)
	{
		// Build clean path to class source
		$classPath = self::$_libDirectory . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
		
		// Class is registered?
		if (file_exists($classPath))
		{
			// Register in tracker
			if (self::$_logClasses)
			{
				self::$_loadedClasses[$className] = $classPath;
			}

			// Require file
			return require_once $classPath;
		}
		
		// We failed to load file
		return false;
	}
	
	/**
	 * Loaded classes list accessor
	 *
	 * @param bool $logClassess
	 */
	public static function logClasses($logClasses)
	{
		return self::$_logClasses = (bool) $logClasses;
	}
}