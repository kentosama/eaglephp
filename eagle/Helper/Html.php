<?php

/**
 * Html helper
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */

namespace Eagle\Helper;

use Eagle\Helper;
use Eagle\Request;
use Eagle\Router;

class Html extends Helper
{
    private $request;

    function __construct()
    {
        $this->request = new Request;
    }

    /**
     * @brief Générer un élement <image>
     * @param $uri Chaîne contenant le lien vers l'image.
     * @param $args Tableau contenant les arguments pour personnaliser l'élement
     * @warning IMG_DIR est utilisé par défaut pour le répertoire courrant.
     * @return string Chaîne contenant le code HTML
     */
    public function image(string $uri, array $args = []): string
    {
        $default =
        [
            'class' => false,
            'alt' => false,
        ];

        $root = ['/', '.'];

        if(in_array(dirname($uri), $root))
            $uri = IMG_DIR . DS . $uri ;
        
        
        
        $args = array_merge($default, $args);
        
        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }

        $args = implode(' ', $data);

        $result = '<img src="$1" $2>';
        $result = str_replace('$1', DS . $uri, $result);
        $result = str_replace('$2', $args, $result);

        return $result;
    }

   /**
     * @brief Créer un élement <button>.
     * @param $name Chaine contenant le nom de l'élément.
     * @param $args Tableau contenant les arguments pour personnaliser l'élément.
     * @return string Chaîne contenant le code HTML
     */
    public function button(string $name, array $args = [])
    {
        $default = [
            'class' => 'btn btn-primary',
        ];

        $args = array_merge($default, $args);

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }

        $html = '<button href="$1" $2>$3</button>';
        $html = str_replace('$2', implode(' ',  $data), $html);
        $html = str_replace('$3', $name, $html);

        return $html;
    }

    /**
     * @brief Créer un élement <button> avec confirmation de suppression d'un enregistrement.
     * @param $name Chaine contenant le nom de l'élément.
     * @param $id Identifiant de l'enregistrement
     * @param $args Tableau contenant les arguments pour personnaliser l'élément.
     * @return string Chaîne contenant le code HTML
     */
    public function buttonDelete(int $id, string $name = 'Delete', array $args = []): string
    {
        $params = $this->request->getParams();
        
        $default = [
            'class'             => 'btn btn-sm btn-danger',
            'data-href'         => sprintf('?controller=%s&action=delete&id=%d', $params['controller'], $id),
            'data-bs-toggle'    => 'modal',
            'data-bs-target'    => '#confirm-delete',
        ];

        $args = array_merge($default, $args);

        return $this->button($name, $args);
    }

    /**
     * @brief Créer un élement <a>.
     * @param $title Chaine contenant le titre du lien.
     * @param $url Chaîne ou tableau contenant l'URL.
     * @param $args Tableau contenant les arguments pour personnaliser le lien.
     * @return string Chaîne contenant le code HTML
     */
    public function link(string $title = NULL, $url = [], array $args = [])
    {
        $default = [
            'alt'       => $title,
            'class'     => NULL,
        ];

        $_url = [
            'prefix' => $this->request->getParams('prefix'),
        ];

        $uri = '';

        if(!empty($url))
        {
            if(is_array($url))
            {
                $url = array_merge($_url, $url);
                $url = Router::parse($url);
            }
                
        }

        $args = array_merge($default, $args);

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }
        
        $params = $this->request->getParams();
        
        if(!empty($params))
        $currentUrl = ['prefix' => $params['prefix'], 'controller' => $params['controller'], 'action' => $params['action']];
        
        if(!empty($url) && is_string($url))
        {
            $uri = $url;
        }
        else if(is_array($url))
        {
            if($url['action'] === 'index')
            {
                $url['action'] = '';
            }

            $url = array_merge($currentUrl, $url);
            
            $uri = '';
            $items = [];
            foreach($url as $key => $value)
                $items[] = $key . '=' . $value;
            
            $uri .= '/' . implode('&', $items);

            $uri = Router::reverse($uri);
        }

        $html = '<a href="$1" $2>$3</a>';
        $html = str_replace('$2', implode(' ',  $data), $html);
        $html = str_replace('$1', $uri, $html);
        $html = str_replace('$3', $title, $html);

        return $html;
    }

    public function css(string $uri, array $args = []): string
    {
        $default = [
            'rel' => 'stylesheet',
            'type' => 'text/css',
        ];

        $args = array_merge($default, $args);

        $data = [];
        foreach($args as $key => $value)
        {
            if(!empty($value))
                $data[] = $key . '="' . $value . '"';
        }

        $data = implode(' ', $data);

        $uri = CSS_DIR . DS . $uri;

        return "<link href=\"$uri\" $data>";
    }


}