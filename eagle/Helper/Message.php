<?php

namespace Eagle\Helper;

use Eagle\Helper;

class Message extends Helper
{

    private $template = [
        'error' => 'alert alert-danger',
        'success' => 'alert alert-success',
    ];

    /**
     * @brief Afficher un message en attente.
     * @params $key Chaîne contenant la clé du message.
     * @return string
     */
    public function fetch(?string $key = 'default'): ?string
    {
        if(!isset($_SESSION['messages'][$key]))
            return NULL;
        
        $messages = $_SESSION['messages'][$key];

        foreach($messages as $id => $message)
        {
            echo '<div class="'.$this->template[$message['type']].'">'.$message['content'].'</div>';
            unset($_SESSION['messages'][$key][$id]);
        }

        return NULL;
    }
}