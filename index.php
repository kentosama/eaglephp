<?php require_once('app.php') ?>
<?php
/**
 * Index file
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */

use Eagle\Core;
use Eagle\Request;

$request = new Request;

$params = $request->getParams();

// Redirect to the default page if params is empty
if(empty($params['controller']))
    Core::redirect(['controller' => 'Pages', 'action' => 'index']);

// Define the controller's action
$action = !empty($params['action']) ? $params['action'] : 'index';

$params['controller'] = ucfirst($params['controller']);

// Create the controller
$controller = 'App\Controller\\' . $params['controller'] . 'Controller';
$controller = new $controller;

// Process 
$controller->view->action = $params['controller'] . DS . $action;
$controller->beforeFilter();
$controller->$action();
$controller->view->render();
?>

