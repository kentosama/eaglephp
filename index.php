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
use Eagle\ErrorException;

ini_set('display_errors', 1);
error_reporting(E_ALL);

$request = new Request;

$params = $request->getParams();


// Redirect to the default page if params is empty
if(empty($params['controller']))
    Core::redirect(['controller' => 'pages', 'action' => 'index']);

// Define the controller's action
$action = !empty($params['action']) ? $params['action'] : 'index';

$args = NULL;
if(!empty($params['pass']))
    $args = implode(', ', $params['pass']);

$params['controller'] = ucfirst($params['controller']);

// Create the controller
$controller = 'App\Controller\\' . '$prefix' . $params['controller'] . 'Controller';

if(!empty($params['prefix']))
{
    $controller = str_replace('$prefix', ucfirst($params['prefix']) . '\\', $controller);
}
else
{
   
    $controller = str_replace('$prefix', '', $controller);

}

try {
   
    // Vérifier si la classe du contrôleur existe (avec son namespace complet)
    if (!class_exists($controller)) {
        
        $error = "Controller '$controller' not found.";
        throw new ErrorException($error, 1);
    }

    $controller = new $controller;

} catch (ErrorException $e) {
    // Gérer l'exception et afficher l'erreur personnalisée
    $error_message = $e->getMessage();
    // Tu pourrais aussi utiliser une vue d'erreur spécifique si besoin
    include TEMPLATE_DIR . DS . 'Layouts' . DS . 'error.php';
    die(); // Arrêter l'exécution
}

// Process 
$controller->view->action = $params['controller'] . DS . $action;
$controller->authorize();
$controller->beforeFilter();
$controller->$action($args);
$controller->view->render();

?>

