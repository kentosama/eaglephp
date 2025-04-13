<?php
namespace Eagle;

class Validation
{
    private $data = NULL;
    private $message = 'This field is not valid';
    public $errors = [];
    public $errorCount = 0;

    function __construct() {}

    /**
     * Valider les données à enregistrer.
     * @param array $data - Données à valider.
     * @param array $rules - Règles de validation.
     * @return void
     */
    public function validate(array $data, array $rules): void
    {
        if (empty($rules)) return;
        
        $this->data = $data;

        foreach ($rules as $field => $ruleSet) {
            foreach ($ruleSet as $rule) {
                if ($this->errorCount > 0) break; // Si une erreur est déjà détectée, on arrête

                if (isset($this->data[$field])) {
                    $this->applyRule($field, $rule);
                }
            }
        }
    }

    /**
     * Appliquer une règle de validation.
     * @param string $field - Le nom du champ à valider.
     * @param array $rule - La règle de validation à appliquer.
     */
    private function applyRule(string $field, array $rule): void
    {
        switch ($rule['rule']) {
            case 'notEmpty':
                $this->notEmpty($field);
                break;
            case 'between':
                $this->between($field, $rule['min'], $rule['max']);
                break;
            case 'maxLenght':
                $this->maxLenght($field, $rule['value']);
                break;
            case 'maxFileSize':
                $this->maxFileSize($field, $rule['value']);
                break;
            case 'integer':
                $this->integer($field);
                break;
            case 'regex':
                $this->regex($field, $rule['value']);
                break;
            case 'email':
                $this->email($field);
                break;
        }
    }

    /**
     * Ajouter une erreur.
     * @param string $field - Le champ en erreur.
     * @param string $message - Le message d'erreur.
     */
    private function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
        $this->errorCount++;
    }

    /**
     * Valider que le champ n'est pas vide.
     * @param string $field - Le champ à valider.
     */
    private function notEmpty(string $field): void
    {
        if (empty($this->data[$field])) {
            $this->addError($field, 'This field can not be empty');
        }
    }

    /**
     * Valider l'email.
     * @param string $field - Le champ à valider.
     */
    private function email(string $field): void
    {
        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Please enter a valid email address');
        }
    }

    /**
     * Valider une expression régulière.
     * @param string $field - Le champ à valider.
     * @param string $value - L'expression régulière.
     */
    private function regex(string $field, string $value): void
    {
        if (!preg_match('/' . $value . '/', $this->data[$field])) {
            $this->addError($field, $this->message);
        }
    }

    /**
     * Valider la longueur minimale.
     * @param string $field - Le champ à valider.
     * @param int $value - La longueur minimale.
     */
    private function minLenght(string $field, int $value): void
    {
        if (strlen($this->data[$field]) < $value) {
            $this->addError($field, sprintf('This field must contain more than %s characters', $value));
        }
    }

    /**
     * Valider la longueur maximale.
     * @param string $field - Le champ à valider.
     * @param int $value - La longueur maximale.
     */
    private function maxLenght(string $field, int $value): void
    {
        if (strlen($this->data[$field]) > $value) {
            $this->addError($field, sprintf('This field must contain less than %s characters', $value));
        }
    }

    /**
     * Valider que la longueur est entre une valeur minimale et maximale.
     * @param string $field - Le champ à valider.
     * @param int $min - Longueur minimale.
     * @param int $max - Longueur maximale.
     */
    private function between(string $field, int $min, int $max): void
    {
        $len = strlen($this->data[$field]);
        if ($len < $min || $len > $max) {
            $this->addError($field, sprintf('This field must contain between %s and %s characters', $min, $max));
        }
    }

    /**
     * Valider que la valeur est un entier.
     * @param string $field - Le champ à valider.
     */
    private function integer(string $field): void
    {
        if (!preg_match('/^[0-9]+$/', $this->data[$field])) {
            $this->addError($field, 'This field can only contain an integer');
        }
    }

    /**
     * Valider la taille maximale d'un fichier.
     * @param string $field - Le champ à valider.
     * @param string $value - La taille maximale (en texte, ex: "2M").
     */
    private function maxFileSize(string $field, string $value): void
    {
        $bytes = Core::toBytes($value);
        if ($this->data[$field]['size'] > $bytes) {
            $this->addError($field, sprintf('File size must be less than %s', $value));
        }
    }
}
