<?php

// No direct access.
defined('_TEXEC') or die;

/**
 * RAT Application define.
 */

//Global definitions.
//T framework path definitions.
$parts = explode(DIRECTORY_SEPARATOR, TPATH_BASE);

//Defines.
define('TPATH_ROOT',			implode(DIRECTORY_SEPARATOR, $parts));

define('TPATH_SITE',			TPATH_ROOT);
define('TPATH_CONFIGURATION',	TPATH_ROOT);
define('TPATH_LIBRARIES',		TPATH_ROOT . DS. 'Libraries');
define('TPATH_CACHE',			TPATH_ROOT . DS. 'Cache');
define('TPATH_MODULE',			TPATH_ROOT . DS. 'Modules');
define('TPATH_TEMPLATES',			TPATH_ROOT . DS. 'Templates');
