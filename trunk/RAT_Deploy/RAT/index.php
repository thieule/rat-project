<?php
/**
 * @package		RAT Soft
 * @author Thieu.LeQuangs
 */
// Set flag that this is a parent file.
define('_TEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

if (file_exists(dirname(__FILE__) . '/defines.php')) {
	include_once dirname(__FILE__) . '/defines.php';
}

define('TPATH_BASE', dirname(__FILE__));
require_once TPATH_BASE.'/includes/defines.php';

require_once TPATH_BASE.'/includes/framework.php';
require_once TPATH_BASE.'/includes/application.php';

$tokenModule = array(
    '2dea96fec20593566ab75692c9949596833adc' => 'user',
    '21232f297a57a5a743894a0e4a801fc3' => 'admin'
    
);


$app = T::getApplication(array('clientId'=>1,'name'=>'TApplication'));

// Instantiate the application.
$app->Initialise($tokenModule);

// Excude the application.
$app->excude();

// Render the application.
echo $app->render();