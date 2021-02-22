<?php

/**
 * Main app core functions
 *
 * @author Jacques Belosoukinski <kentosama@free.fr>
 * 
 */

namespace Eagle;

class Core
{
    /**
     * @brief Rediriger l'URL.
     * @param $url Tableau contenant la clé / valeur pour le controleur et son action.
     * @return void
     */
    public static function redirect(array $url): void
    {
        $request = new Request;
        $args = [
            'controller' => $request->getParams('controller'),
            'action' => $request->getParams('action'),
        ];

        $url = array_merge($args, $url);

        if(empty($url['action']))
            $url['action'] = 'index';

        header(sprintf('Location: /?controller=%s&action=%s', $url['controller'], $url['action']));
        exit();
    }

    /**
     * @brief Hacher un mot de passe.
     * @param $password Chaîne de caractère contenant le mot de passe à sécuriser.
     * @return string
     */
    public static function passwordHash($password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @brief Vérifier un mot de passe.
     * @param $password Chaîne de caractère contenant le mot de passe à vérifier.
     * @param $hash Chaîne de caractère contenant le mot de passe hacher à comparer.
     * @return bool FALSE si la vérification à échouée.
     */
    public static function passwordVerify($password, $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * @brief Récupérer la configuration de l'application
     * @return array
     */
    public static function getConfig(): array
    {
        return include APP_CONFIG . DS . 'app.php';
    }

    /**
     * @brief Convertir une chaîne de caractère en slug.
     * @param $str La chaîne de caractère à slugifier.
     * @return string
     */
    public static function slugify(string $str): string
    {

        $str = preg_replace('~[^\pL\d]+~u', '-', $str);
        $str = preg_replace('~[^-\w]+~', '', $str);
        $str = trim($str, '-');

        $str = str_replace(' ', '-', $str);
        $str = strtolower($str);

        return $str;
    }

    /**
     * @brief Convertir la taille d'un fichier en bytes.
     * @param $value Chaîne contenant la taille d'un fichier.
     * @return int
     */
    public static function toBytes(string $value): ?int 
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $number = substr($value, 0, -2);
        $suffix = strtoupper(substr($value, -2));
    
        //B or no suffix
        if(is_numeric(substr($suffix, 0, 1))) {
            return preg_replace('/[^\d]/', '', $value);
        }
    
        $exponent = array_flip($units)[$suffix] ?? NULL;
        if($exponent === NULL) {
            return NULL;
        }
    
        return $number * (1024 ** $exponent);
    }
}