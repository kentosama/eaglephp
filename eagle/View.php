<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

class View
{
    public $request; /**< Instance de Request */
    public $vars = []; /**< Tableau contenant les variables de la vue */
    public $layout; /**< Chaîne contenant le nom du layout */
    public $action; /**< Chaîne contenant le nom de l'action */

    private $helpers = [
        'Html',
        'Form',
        'Message',
    ];

    private $globals = [
        'title' => FALSE,
    ];

    public function __construct()
    {
        $this->request = new Request();
        $this->layout = 'default';

        foreach($this->helpers as $helper)
        {
            $className = 'Eagle\\Helper\\'.$helper;
            $helper = strtolower(str_replace('Helper', '', $helper));
            $this->$helper =  new $className;
        }
    }

    /**
     * @brief Assigner une variable dans la vue
     * @param $name Chaîne contenant le nom de la variable
     * @param $value Chaîne contenant la valeur de la variable.
     * @return void
     */
    public function assign(string $name, $value = NULL): void
    {
        $this->globals[$name] = $value;
    }

    /**
     * @brief Afficher une variable de la vue
     * @param $name Chaîne contenant le nom de la variable.
     * @return mixed
     */
    public function fetch(string $name)
    {
        if(isset($this->globals[$name]))
        return $this->globals[$name];

        return NULL;
    }

    /**
     * @brief Afficher le contenu de la vue
     * @return void
     */
    private function content(): void
    {
        extract($this->vars);
        include TEMPLATE_DIR . DS . $this->action . '.php';
    }


    /**
     * @brief Afficher la vue
     * @return void
     */
    public function render(): void
    {
        if($this->layout)
        {
            extract($this->vars);
            include TEMPLATE_DIR . DS . 'Layouts' . DS . $this->layout . '.php'; 
        }
        else
        {
            $this->content();
        }
       
        unset($_SESSION['errors']);
    }

    /**
     * Afficher un element dans la vue
     * @param $name Chaîne contenant le nom de l'élément
     * @param $vars Tableau contenant les variables à passer vers l'élément
     * @return void
     */
    public function element(string $name, ?array $vars = []): void
    {
        extract($vars);
        include TEMPLATE_DIR . DS . 'Elements' . DS . $name . '.php';
    }

    
}