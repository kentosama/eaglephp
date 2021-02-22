<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

use Throwable;
use Exception;

class ErrorException extends Exception
{
    protected $message = 'Unknow exception';

    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @brief Recupérer 10 lignes dans le code source au dessus de la ligne concernée.
     * @return array
     */
    public function getMultipleLineCode(): ?array
    {
        $lines = NULL;
        $start = $this->line - 10;
        $end = $this->line;

        if(file_exists($this->file))
        {
            $count = 0;
            foreach(file($this->file) as $line)
            {   
                $count++;

                if($count >= $start)
                {
                    $lines[] = htmlentities($line);
                    
                    if($count === $end)
                    break;
                }
            }
        }
        return $lines;
    }

    /**
     * @brief Recupérer une ligne dans le code source.
     * @return string
     */
    public function getlineCode(): string
    {
        $line = '';
        
        if(file_exists($this->file))
        {
            $count  = 0;
            foreach(file($this->file) as $line)
            {   
                $count++;
                if($count === $this->line)
                break;
            }
        }
        return $line;
    }
}