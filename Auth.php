<?php

namespace Eagle;

/**
 * La classe Auth gère l'authentification des utilisateurs.
 */

class Auth
{
    private $core;

    private $_config = [
        'entity'    => 'Users',
        'fields'    => ['user_nickname', 'user_password'],
        'redirect'  => ['controller' => 'users', 'action' => 'login'],
        'message'   => 'You are not allowed to view this page'
    ];
    

    function __construct()
    {
        $this->core = new Core;   
    }

    /**
     * @brief Défini la configuration pour l'authentification
     * @param $config Tableau contenant les paramètres de la configuration de l'authentification :\n
     * string <b>entity</b> Nom de l'entité. Par défaut 'Users'.\n
     * array <b>fields</b> Tableau indiquant les champs à utiliser pour vérification. Par défaut '['user_nickname', 'user_password'].\n
     * array <b>redirect</b> Tableau comprennant le nom du controller et son action afin de redigirer les utilisateurs non authentifiés. 
     * Par défaut '['controller' => 'Users', 'action' => 'login']'.\n
     * string <b>message</b> Chaîne à afficher lorsqu'un utilisateur non authentitifié tente d'accéder à une page non autorisée.
     * @return void
     */
    function setConfig(array $config): void
    {   
        $this->_config = array_merge($this->_config, $config);
    }

    /**
     * @brief Identifie un utilisateur
     * @param $data Tableau contenant la valeur des champs définis dans la configuration de l'Authentification.
     * @return bool TRUE si l'authentification a réussie.
     * @see setUser()
     */
    function identify(array $data): bool
    {
        $className = 'App\\Entity\\'.$this->_config['entity'];
        $query = new $className;

        $user = $query->findFirst(['conditions' => [$this->_config['fields'][0] => $data[$this->_config['fields'][0]]]]);

        if(!$user)
        return FALSE;

        if($this->core->passwordVerify($data[$this->_config['fields'][1]], $user[$this->_config['fields'][1]]))
        {
            $this->setUser($user);
            return TRUE;
        }
            
        return FALSE;
     
    }   
    
    /**
     * @brief Déconnecte l'utilisateur
     * @return void
     */
    function logout(): void
    {
        if(isset($_SESSION['user']))
        unset($_SESSION['user']);
    }

    /**
     * @brief Return l'URL de redirection défini dans la configuration de l'authentification.
     * @return string Chaîne de caractère contenant l'URL de redirection.
     * @see setConfig()
     */
    function redirect(): ?array
    {
        return $this->_config['redirect'];
    }

    /**
     * @brief Connecte un utilisateur manuellement.
     * @param $user Tableau contenant les données du compteur utilisateur (id, nickname, picture, etc.)
     * @return void
     * @see identify()
     */
    function setUser(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    /**
     * @brief Récupère les informations de l'utilisateur connecté dans la session.
     * @param $key Une chaîne indiquant la clé afin de récupérer directement la valeur souhaitée.
     * @return string || array || NULL
     */
    function user(string $key = NULL)
    {
        if(!isset($_SESSION['user']))
        return NULL;

        if(!empty($key))
        {
            if(isset($_SESSION['user'][$key]))
                return $_SESSION['user'][$key];

            return NULL;
        }

        return $_SESSION['user'];
    }

}