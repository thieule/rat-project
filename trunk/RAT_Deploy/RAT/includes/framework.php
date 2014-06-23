<?php
// No direct access.
defined('_TEXEC') or die;

//
// T system checks.
//

@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', '0');


//
// T system startup.
//

// System includes.
require_once TPATH_LIBRARIES.'/tinclude.php';

// Force library to be in TError legacy mode
TError::$legacy = true;
TError::setErrorHandling(E_NOTICE, 'message');
TError::setErrorHandling(E_WARNING, 'message');
TError::setErrorHandling(E_ERROR, 'callback', array('TError', 'customErrorPage'));

// Pre-Load configuration.
ob_start();
require_once TPATH_CONFIGURATION.'/configuration.php';
ob_end_clean();

// System configuration.
$config = new TConfig();

// Set the error_reporting
switch ($config->error_reporting)
{
	case 'default':
	case '-1':
		break;

	case 'none':
	case '0':
		error_reporting(0);
		break;

	case 'simple':
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set('display_errors', 1);
		break;

	case 'maximum':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		break;

	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
		break;

	default:
		error_reporting($config->error_reporting);
		ini_set('display_errors', 1);
		break;
}

unset($config);



//
// T library imports.
//

tinclude('core.environment.uri');
tinclude('core.environment.request');
tinclude('core.utilities.utility');
tinclude('core.event.dispatcher');
tinclude('core.utilities.arrayhelper');
tinclude('core.database.table');
//tinclude('core.session.session');
