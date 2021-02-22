<?php

/**
 * Main app
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */



session_start();

// Core
require_once('config/paths.php');
/*require_once(APP_CORE . DS . 'core.php');
require_once(APP_CORE . DS . 'validation.php');
require_once(APP_CORE . DS . 'query.php');
require_once(APP_CORE . DS . 'request.php');
require_once(APP_CORE . DS . 'auth.php');*/

spl_autoload_register(function ($className) {
    $className = str_replace('\\', DS, $className);
    $className = str_replace('Eagle', 'eagle', $className);
    $className = str_replace('App/', 'app/', $className);
    
    include $className . '.php';
});

use Eagle\Core;
use Eagle\Query;
use Eagle\Message;
use Eagle\ErrorException;



$config = Core::getConfig();

if($config['debug'])
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
   
}
else
{
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}



$query = new Query;
$message = new Message;

// Check database
try
{
    if(!$query->isConnected())
    {
        $error = 'Unable to connect to the database';
        throw new ErrorException($error, 1);
    }
}
catch(Exception $e)
{
    include TEMPLATE_DIR . DS . 'Layouts' . DS . 'error.php';
    die();
}


