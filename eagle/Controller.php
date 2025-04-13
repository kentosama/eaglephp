<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;



class Controller
{
    public $view;       /**< Instance de View */
    public $request;    /**< Instance de Request */
    public $message;    /**< Instance de Message */
    public $model;      /**< Instance de Query */
    public $name;       /**< Nom du Controller */
    public $auth;
    //public $auth;        /**< Instance de Auth */

    public function __construct()
    {
        
        $this->request = new Request();
        $this->view = new View();
        $this->message = new Message();
        $this->auth = new Auth();
        
        $this->initialize();
    }

    /**
     * @brief Initialiser le Controller
     * @warning La fonction est appellée en premier à la construction du Controller
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * @brief Fonction de rappelle exécutée avant l'affichage de la vue.
     * @return void
     */
    public function beforeFilter(): void 
    {

    }

    /**
     * @brief Définir une variable dans la vue.
     * @param $name Chaîne contenant le nom de la variable.
     * @param $value Chaîne contenant la valeur de la variable.
     * @return void
     */
    public function set($name, $value): void
    {
        $this->view->vars[$name] = $value;
    }

    /**
     * @brief Rediriger immédiatement la page.
     * @param $url Tableau contenant la clé 'controller' et 'action'.
     * @return void
     */
    public function redirect(array $url): void
    {
        $args = [
            'prefix' => $this->request->getParams('prefix'),
            'controller' => $this->request->getParams('controller'),
            'action' => $this->request->getParams('action'),
        ];
        
        $url = array_merge($args, $url);
        $url = Router::parse($url, FALSE);

        if (is_array($url)) 
        {
            
            $items = [];
            foreach ($url as $key => $value) {
                if (!empty($value))
                $items[] = $key . '=' . $value;
            }
            
            $url = '/' . implode('&', $items);
            
            $url = Router::reverse($url);
        }
       
        header('Location: ' . $url);
        exit();
    }

    /**
     * @brief Charger une classe Entity dans le Controller.
     * @param $name Chaîne contenant le nom de l'Entity.
     * @return void
     */
    public function loadEntity($name): void
    {
        $entity = strtolower($name);
        $class = 'App\\Entity\\'.$name;
        $this->$entity = new $class; 
    }

    public function authorize(): void
    {
        if($this->auth)
        {
            if(empty($this->auth->user()))
            { 
                if(!$this->auth->authorized())
                {
                    $this->message->error('You are not allowed to be here!', 'auth');
                    $this->redirect($this->auth->redirect());
                }
            }    
        }
    }
}