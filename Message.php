<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

class Message
{
    private $template = [
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
    ];

    /**
     * @brief Afficher un message avec le template success.
     * @param $message Chaîne contenant le message.
     * @param $key Chaîne contenant la clé pour personnaliser l'affichage.
     * @return void
     */
    public function success($message, $key = 'default'): void
    {
        $msg = ['content' => $message, 'type' => 'success'];
        $_SESSION['messages'][$key][] = $msg;
    }

     /**
     * @brief Afficher un message avec le template error.
     * @param $message Chaîne contenant le message.
     * @param $key Chaîne contenant la clé pour personnaliser l'affichage.
     * @return void
     */
    public function error($message, $key = 'default'): void
    {
     
        $msg = ['content' => $message, 'type' => 'error'];
        $_SESSION['messages'][$key][] = $msg;
    }
}