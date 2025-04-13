<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

use Throwable;
use Exception;

class ErrorException extends Exception
{
    protected $message = 'Unknown exception';

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?: $this->message, $code, $previous);
    }

    /**
     * Récupère 10 lignes de code avant la ligne concernée.
     *
     * @return array|null
     */
    public function getMultipleLineCode(): ?array
    {
        if (!file_exists($this->file)) {
            return null;
        }

        $lines = [];
        $start = max(1, $this->line - 10);
        $end = $this->line;
        $count = 0;

        foreach (file($this->file) as $line) {
            $count++;
            if ($count >= $start) {
                $lines[] = htmlentities($line, ENT_QUOTES, 'UTF-8');
                if ($count === $end) break;
            }
        }

        return $lines;
    }

    /**
     * Récupère la ligne de code source à l'origine de l'erreur.
     *
     * @return string
     */
    public function getLineCode(): string
    {
        if (!file_exists($this->file)) {
            return '';
        }

        $count = 0;
        foreach (file($this->file) as $line) {
            $count++;
            if ($count === $this->line) {
                return htmlentities($line, ENT_QUOTES, 'UTF-8');
            }
        }

        return '';
    }
}
