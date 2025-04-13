<?php 

require_once __DIR__ . '/config/paths.php';
require_once __DIR__ . '/vendor/autoload.php';

use Eagle\ErrorException;
use Eagle\Core;
use Eagle\Query;
use Eagle\Message;
use Eagle\Configure;

session_start();

$config = Core::getConfig();
foreach($config as $name => $value)
    Configure::write($name, $value);

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

require_once APP_CONFIG . DS . 'routes.php';
