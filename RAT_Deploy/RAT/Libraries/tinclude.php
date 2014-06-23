<?php
/**
 * @package    T.Platform
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Set the platform root path as a constant if necessary.
if (!defined('T_PLATFORM'))
{
	define('T_PLATFORM', dirname(__FILE__));
}

// Set the directory separator define if necessary.
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));
if (!defined('IS_WIN'))
{
	define('IS_WIN', ($os === 'WIN') ? true : false);
}
if (!defined('IS_MAC'))
{
	define('IS_MAC', ($os === 'MAC') ? true : false);
}
if (!defined('IS_UNIX'))
{
	define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')) ? true : false);
}


// Import the library loader if necessary.
if (!class_exists('TLoader'))
{
	require_once T_PLATFORM . '/loader.php';
}

class_exists('TLoader') or die;

// Setup the autoloaders.
TLoader::setup();

/**
 * Import the base Joomla Platform libraries.
 */

// Import the t library.
TLoader::tinclude('core.t');

// Import the exception and error handling libraries.
TLoader::tinclude('core.error.exception');

/*
 * If the HTTP_HOST environment variable is set we assume a Web request and
 * thus we import the request library and most likely clean the request input.
 */
if (isset($_SERVER['HTTP_HOST']))
{
	TLoader::register('JRequest', T_PLATFORM . '/core/environment/request.php');

	// If an application flags it doesn't want this, adhere to that.
	if (!defined('_TREQUEST_NO_CLEAN') && (bool) ini_get('register_globals'))
	{
		TRequest::clean();
	}
}

// Import the base object library.
TLoader::tinclude('core.base.object');

// Register classes that don't follow one file per class naming conventions.
TLoader::register('Text', T_PLATFORM . '/core/methods.php');
