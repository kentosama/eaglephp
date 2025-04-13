<?php
namespace Eagle;

use Exception;
use ErrorException;

use Eagle\Helper\Html;
use Eagle\Helper\Form;
use Eagle\Helper\Message;
use Eagle\Helper\Text;

class View
{
    public $request; /**< Instance de Request */
    public $vars = []; /**< Tableau contenant les variables de la vue */
    public $layout; /**< Chaîne contenant le nom du layout */
    public $action; /**< Chaîne contenant le nom de l'action */


    public Html $html;
    public Form $form;
    public Message $message;
    public Text $text;

    public function __construct()
    {
        $this->request = new Request();
        $this->layout = 'default';

        // Chargement des helpers
        $this->html = new Html();
        $this->form = new Form();
        $this->message = new Message();
        $this->text = new Text();
    }

    /**
     * Assigner une variable dans la vue
     * @param string $name - Nom de la variable
     * @param mixed $value - Valeur de la variable
     */
    public function assign(string $name, $value = NULL): void
    {
        $this->vars[$name] = $value;
    }

    /**
     * Afficher une variable de la vue
     * @param string $name - Nom de la variable
     * @return mixed
     */
    public function fetch(string $name)
    {
        return $this->vars[$name] ?? NULL;
    }

    /**
     * Afficher le contenu de la vue
     * @return void
     */
    private function content(): void
    {
        $prefix = $this->request->getParams('prefix');
        $templateFile = $prefix ? TEMPLATE_DIR . DS . ucfirst($prefix) . DS . $this->action . '.php' : TEMPLATE_DIR . DS . $this->action . '.php';

        if (file_exists($templateFile)) {
            extract($this->vars);
            include $templateFile;
        } else {
            $this->renderError('Template not found: ' . $templateFile);
        }
    }

    /**
     * Afficher la vue avec ou sans layout
     * @return void
     */
    public function render(): void
    {
        if ($this->layout) {
            extract($this->vars);
            include TEMPLATE_DIR . DS . 'Layouts' . DS . $this->layout . '.php'; 
        } else {
            $this->content();
        }
        unset($_SESSION['errors']);
    }

    /**
     * Afficher un élément dans la vue (ex: un composant réutilisable)
     * @param string $name - Nom de l'élément
     * @param array|null $vars - Variables à passer à l'élément
     * @return void
     */
    public function element(string $name, ?array $vars = []): void
    {
        $filename = TEMPLATE_DIR . DS . 'Elements' . DS . $name . '.php';

        if (!file_exists($filename)) {
            $this->renderError("Element '$name' not found");
        } else {
            extract($vars);
            include $filename;
        }
    }

    /**
     * Afficher une page d'erreur
     * @param string $message - Message d'erreur
     * @return void
     */
    private function renderError(string $message): void
    {
        // Enregistrer l'erreur et afficher une page d'erreur générique
        error_log($message);
        $e = new ErrorException($message);
        include TEMPLATE_DIR . DS . 'Layouts' . DS . 'error.php';
        exit();
    }
}
