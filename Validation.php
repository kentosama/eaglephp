<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

class Validation
{

    private $data = NULL;
    private $message = 'This field is not valid';
    public $errors = [];
    public $errorCount = 0;


    function __construct()
    {
    }

    /**
     * Le compteur d'erreurs est incrémenté chaque fois qu'une règle n'est pas satisfaite.
     * @brief Valider les données à enregistrer.
     * @param $data Tableau contenant les données à valider.
     * @param $rules Tableau contenant les règles à traiter.
     * @return void
     */
    public function validate(array $data, array $rules): void
    {
        if(empty($rules))
        return;
        
        $this->data = $data;

        foreach ($rules as $field => $r) {
            $count = $this->errorCount;
            foreach ($r as $rule) {
                
                if ($count < $this->errorCount)
                    break;

                if(isset($this->data[$field]))
                {
                    if ($rule['rule'] === 'notEmpty')
                        $this->notEmpty($field);
                    else if ($rule['rule'] === 'between')
                        $this->between($field, $rule['min'], $rule['max']);
                    else if($rule['rule'] === 'maxLenght')
                        $this->maxLenght($field, $rule['value']);
                    else if($rule['rule'] === 'maxFileSize')
                        $this->maxFileSize($field, $rule['value']);
                    else if($rule['rule'] === 'integer')
                        $this->integer($field);
                    else if($rule['rule'] === 'regex')
                        $this->regex($field, $rule['value']);
                }
            }
        }
    }

    private function notEmpty(string $field)
    {
        if(empty($this->data[$field]))
        {
            $this->errors[$field] = 'This field can not be empty';
            $this->errorCount++;
        }
    }

    private function email(string $field)
    {

    }

    private function regex(string $field, string $value, string $message = NULL)
    {
        
        if(!preg_match('/'.$value.'/', $this->data[$field]))
        {
            $this->errors[$field] = !$message ? $this->message : $message;
            $this->errorCount++;
        }
    }

    private function minLenght(string $field, int $value)
    {
        if(strlen($this->data[$field]) < $value)
        {
            $this->errors[$field] = sprintf('This field must contain more than %s characters', $value);
            $this->errorCount++;
        }
    }

    private function maxLenght(string $field, int $value)
    {
        if(strlen($this->data[$field]) > $value)
        {
            $this->errors[$field] = sprintf('This field must contain less than %s characters', $value);
            $this->errorCount++;
        }
    }

    private function between(string $field, int $min, int $max)
    {
        $len = strlen($this->data[$field]);
        if($len < $min || $len > $max)
        {
            $this->errors[$field] = sprintf('This field must contain between %s and %s characters', $min, $max);
            $this->errorCount++;
        }
    }

    private function isUnique(string $field, string $key)
    {
        
    }

    private function integer(string $field)
    {
        if(!preg_match('/^[0-9]+$/', $this->data[$field]))
        {
            $this->errors[$field] = sprintf('This field can only contain an integer');
            $this->errorCount++;
        }
    } 

    private function maxFileSize(string $field, string $value)
    {
        $bytes = Core::toBytes($value);
        if($this->data[$field]['size'] > $bytes)
        {
            $this->errors[$field] = sprintf('File size must be less than %s ', $value);
            $this->errorCount++;
        }
    }

}