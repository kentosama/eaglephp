<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

class Configure
{
    protected static $values = [];

    /**
     * @brief Ecrire une entrée dans la configuration avec une paire clé / valeur.
     * @param $key Chaîne contenant la clé.
     * @param $value Valeur à écrire
     * @return void
     */
    public static function write(string $key, $value = NULL) : void
    {
        static::$values[$key] = $value;
    }

    /**
     * @brief Lire une entrée dans la configuration
     * @param $key Chaîne contenant la clé.
     * @param $default Valeur à retourner par défault.
     * @return mixed
     */
    public static function read(string $key, $default = NULL)
    {
        if(isset(static::$values[$key]))
        return static::$values[$key];

        return $default;
    }
}