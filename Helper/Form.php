<?php

/**
 * Form helper
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */

namespace Eagle\Helper;

use Eagle\Helper;
use Eagle\Request;

class Form extends Helper
{

    private $entity = NULL;
    private $request;

    function __construct()
    {
        $this->request = new Request;
    }

    /**
     * @brief Créer un formulaire.
     * @param $entity Tableau contenant une Entity.
     * @param $args Tableau contenant les arguments pour personnaliser le formulaire:
     * @li <b>id</b> Identifiant du formulaire.
     * @li <b>method</b> Method à utiliser pour le formulaire (POST ou QUERY).
     * @li <b>enctype</b> Type d'encription du formulaire.\n
     * Il est possible d'utiliser d'autres arguments à votre convenance.
     * @return string Chaîne contenant le code HTML
     */
    public function create(?array $entity = [], ?array $args = []): string
    {
        $this->entity = $entity; 

        $default = [
            'id'        => FALSE,
            'method'    => 'POST',
            'enctype'   => FALSE
        ];

        $args = array_merge($default, $args);

        if (isset($args['type'])) {
            if ($args['type'] === 'file') {
                $args['enctype'] = 'multipart/form-data';
            }

            unset($args['type']);
        }
            
        
        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }

        $content = implode(' ', $data);

        $html = '<form $1>';
        $html = str_replace('$1', $content, $html);
        return $html;
    }

    /**
     * @brief Terminer un formulaire.
     * @return string Chaîne contenant le code HTML
     */
    public function end():string
    {
        $this->entity = NULL;
        return '</form>';
    }

    /**
     * @brief Créer un élement submit.
     * @param $name Chaine contenant le nom du bouton.
     * @param $args Tableau contenant les arguments pour personnaliser le button.
     * @return string Chaîne contenant le code HTML
     */
    public function submit(string $name, ?array $args = [])
    {
        $default = [
            'class' => 'btn btn-primary'
        ];

        $args = array_merge($default, $args);

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }
        $html = '<div class="form-group submit"><input type="submit" value="'.$name.'" '.implode(' ', $data).'></div>';
        return $html;
    }

     /**
     * @brief Créer un élement input.
     * @param $name Chaine contenant le nom du champ.
     * @param $args Tableau contenant les arguments pour personnaliser l'input :
     * @li <b>id</b> Identifiant du champ.
     * @li <b>name</b> Nom du champ. Par défaut utilise l'identifiant
     * @li <b>enctype</b> Classes du champ. Par défaut à 'form-control'.
     * Il est possible d'utiliser d'autres arguments à votre convenance.
     * @return string Chaîne contenant le code HTML
     */
    public function input(string $name, ?array $args = []): string
    {
        $default =
        [
            'name' => $name,
            'id' => $name,
            'type' => 'text',
            'class' => 'form-control',
            'placeholder' => false,
            'label' => false,
        ];

        $args = array_merge($default, $args);

        if(!$this->request->is('POST'))
        {
            if(empty($args['value']) && !in_array($args['type'], ['password', 'file']) && isset($this->entity[$args['name']]))
            {
                $args['value'] = $this->entity[$args['name']];
            }
        }
        else
        {
            if($args['type'] !== 'file')
                $args['value'] = $this->request->getData($args['name']);
        }

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }

        $content = implode(' ', $data);

        $label = !empty($args['label']) ? $args['label'] : $args['id'];


        $input = '<input $1>';
        $input = str_replace('$1', $content, $input);

        
        if(isset($_SESSION['errors'][$args['name']]))
        {
            $input .= sprintf('<div class="input-error">%s</div>', $_SESSION['errors'][$args['name']]);
        }
        
        $html = '<div class="form-group $1"><label for="' . $args['id'] . '">' . $label . '</label>$2</div>';
        $html = str_replace('$1', $args['type'], $html);
        $html = str_replace('$2', $input, $html);

        return $html;

    }

    /**
     * @brief Créer un élément textarea.
     * @param $name Chaine contenant le nom du champ.
     * @param $args Tableau contenant les arguments pour personnaliser l'input :
     * @li <b>id</b> Identifiant du champ.
     * @li <b>name</b> Nom du champ. Par défaut utilise l'identifiant
     * @li <b>enctype</b> Classes du champ. Par défaut à 'form-control'.
     * Il est possible d'utiliser d'autres arguments à votre convenance.
     * @return string Chaîne contenant le code HTML
     */
    public function textarea(string $name, ?array $args = []): string
    {
        $default =
        [
            'name' => $name,
            'id' => $name,
            'class' => 'form-control',
            'placeholder' => false,
            'label' => false,
            'value' => false,
        ];

        $args = array_merge($default, $args);

        if (!$this->request->is('POST')) {
            if (empty($args['value']) && isset($this->entity[$args['name']])) {
                $args['value'] = $this->entity[$args['name']];
            }
        } else {
            $args['value'] = $this->request->getData($args['name']);
        }

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value) && $key != 'value')
                $data[] = $key . '="' . $value . '"';
        }


        $content = implode(' ', $data);
        $label = !empty($args['label']) ? $args['label'] : $args['id'];

        $input = '<textarea $1>$2</textarea>';
        $input = str_replace('$2', $args['value'], $input);
        $input = str_replace('$1', $content, $input);

        if(isset($_SESSION['errors'][$args['name']]))
            $input .= sprintf('<div class="input-error">%s</div>', $_SESSION['errors'][$args['name']]);
        
        
        $html = '<div class="form-group $1"><label for="' . $args['id'] . '">' . $label . '</label>$2</div>';
        $html = str_replace('$1', 'textarea', $html);
        $html = str_replace('$2', $input, $html);

        return $html;
    }

    /**
     * @brief Créer un élément select.
     * @param $name Chaine contenant le nom du champ.
     * @param $options Tableau contenant les éléments options du champ.
     * @param $args Tableau contenant les arguments pour personnaliser l'input :
     * @li <b>id</b> Identifiant du champ.
     * @li <b>name</b> Nom du champ. Par défaut utilise l'identifiant
     * @li <b>enctype</b> Classes du champ. Par défaut à 'form-control'.
     * Il est possible d'utiliser d'autres arguments à votre convenance.
     * @return string Chaîne contenant le code HTML
     */
    public function select(string $name, array $options = [], ?array $args = [])
    {
        $default =
        [
            'name' => $name,
            'id' => $name,
            'class' => 'form-control',
            'label' => false,
            'value' => false,
            'empty' => false
        ];

        $args = array_merge($default, $args);
        if (!$this->request->is('POST')) {
            if (empty($args['value']) && isset($this->entity[$args['name']])) {
                $args['value'] = $this->entity[$args['name']];
            }
        } else {
            $args['value'] = $this->request->getData($args['name']);
        }

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value) && $key != 'value' && $key != 'empty' && $key != 'label')
                $data[] = $key . '="' . $value . '"';
        }

        $opts = [];
        foreach ($options as $key => $value) {

            $selected = ($key === (int) $args['value']) ? ' selected' : '';
            $opts[] = '<option value="' . $key . '"' . $selected . '>' . ucfirst($value) . '</option>';
        }

        $opts = implode('', $opts);


        $content = implode(' ', $data);
        $label = !empty($args['label']) ? $args['label'] : $args['id'];

        $input = '<select $1>$2</select>';
        $input = str_replace('$2', $opts, $input);
        $input = str_replace('$1', $content, $input);

        if(isset($_SESSION['errors'][$args['name']]))
            $input .= sprintf('<div class="input-error">%s</div>', $_SESSION['errors'][$args['name']]);
        
        $html = '<label for="' . $args['id'] . '">' . $label . '</label><div class="form-group $1">$2</div>';
        $html = str_replace('$1', 'select', $html);
        $html = str_replace('$2', $input, $html);

        return $html;
    }
}