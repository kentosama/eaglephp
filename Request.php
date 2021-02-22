<?php

/**
 * App Request
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */

namespace Eagle;

class Request
{

    /**
     * @brief Récupérer les paramètres contenus dans l'URL
     * @param $name Chaîne contenant le nom du paramètre à récupérer.
     * @return array || string 
     */
    public function getParams($name = NULL)
    {
        $keys = ['controller', 'action', 'id'];

        $params = [];
        
        foreach($keys as $key)
        {
            if(isset($_GET[$key]))
                $params[$key] = $_GET[$key];
        }

        if($name)
        {
            if(isset($params[$name]))
                return $params[$name];

            return NULL;
        }

        return $params;
    }

    /**
     * @brief Verifier la méthode de la requête.
     * @param string Chaîne contenant la méthode à vérifier: POST, QUERY...
     * @return bool
     */
    public function is(string $request): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === $request);   
    }

    /**
     * @brief Récupérer les données passées dans la requête POST
     * @param $name Chaîne contenant le nom du champ à récupérer.
     * @return array | string
     */
    public function getData($name = NULL)
    {
        $data = array_merge($_POST, $_FILES);


        if($name)
        {
            if(isset($data[$name]))
                return $data[$name];

            return NULL;
        }

        return $data;
    }

    /**
     * @brief Récupérer les données passées dans la requête QUERY
     * @param $name Chaîne contenant le nom du champ à récupérer.
     * @return array | string
     */
    public function getQuery($name = NULL)
    {
        $data = $_GET;

        if($name)
        {
            if(isset($data[$name]))
                return $data[$name];

            return NULL;
        }

        return $data;
    }


}