<?php

/**
 * App Request
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */

namespace Eagle;

use Eagle\ErrorException;

class Request
{

    /**
     * @brief Récupérer les paramètres contenus dans l'URL
     * @param $name Chaîne contenant le nom du paramètre à récupérer.
     * @return array || string 
     */
    public function getParams(?string $name = NULL)
    {

        $prefixes = ['/', 'admin'];
        $params = [
            'prefix' => FALSE
        ];

        $uri = Router::parse($_SERVER['REQUEST_URI']);
        
        //var_dump($_SERVER['REQUEST_URI']);
        if (is_string($uri)) 
        {
            $uri = explode('/', $_SERVER['REQUEST_URI']);

            if (!empty($uri[1])) 
            {
                if ($uri[1][0] !== '?') 
                {
                    $value = FALSE;
                    foreach ($prefixes as $prefix) {
                        if ($prefix === $uri[1]) {
                            $value = $prefix;
                            break;
                        }
                    }

                    if (!$value) 
                    {
                        $params['controller'] = $uri[1];

                        if (isset($uri[2]))
                            $params['action'] = $uri[2];
                        
                        if(isset($uri[3]))
                        {
                            if($uri[3][0] === '?')
                            {
                                $query = str_replace('?', '', $uri[3]);
                                $query = explode('&', $query);
                                foreach($query as $str)
                                {
                                    $array = explode('=', $str);
                                    $params['query'][$array[0]] = $array[1];
                                }
                                
                            }
                            else
                            {
                                $params['pass'][] = $uri[3];
                            }
                        }
                            
                    } 
                    else 
                    {
                        $params['prefix'] = $value;

                        if (isset($uri[2]))
                            $params['controller'] = $uri[2];

                        if (isset($uri[3]))
                            $params['action'] = $uri[3];

                        if(isset($uri[4]))
                        {
                            if($uri[4][0] === '?')
                                $params['query'] = explode('&', $uri[4]);
                        }
                    }
                }
            }
        } 
        else if(is_array($uri))
        {
            $params = array_merge($params, $uri);
        }

        if(empty($params['controller']))
        $params['controller'] = 'pages';

        if(empty($params['action']))
        $params['action'] = 'index';

        $keys = ['controller', 'action', 'id', 'prefix'];
        
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

        //var_dump($params);die();
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