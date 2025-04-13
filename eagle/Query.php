<?php
/**
 * @author Jacques Belosoukinski <kentosama@free.fr>
 */
namespace Eagle;

use PDO;
use Exception;
use stdClass;

class Query
{

    private $config = [];
    private $connected = FALSE;
    private $sql = [];

    public $table; /**< string Nom de la table de l'entité */
    public $validation; /**< Validation */
    public $rules = []; /**< array Tableau contenant les règles pour la validation */
    public $db; /**< PDO */
    public $associations = []; /**< array Tableau contenant les associations de l'entité */
    public $primaryKey; /**< string Chaîne contenant le nom de la clé primaire */
    public $primaryValue; /**< string Chaîne contenant du champ à utiliser pour l'affichage par défaut */
    public $fields; /**< array Tableau contenant la liste des champs et le type de l'entité */
    
    public function __construct()
    {
        $app_config = include APP_CONFIG . DS . 'app.php';
        $this->config = $app_config['mysql'];

        try
        {
            $dns = sprintf('%s:dbname=%s;host=%s;', $this->config['driver'], $this->config['database'], $this->config['host']);
            $this->db = new PDO($dns, $this->config['username'], $this->config['password']);
            $this->connected = TRUE; 
        }
        catch(Exception $e)
        {
            $this->connected = FALSE;
        }

        $this->validation = new Validation;

        $this->initialize();
    }

    public function initialize(): void
    {
        /*if(!empty($this->associations))
        {
            foreach($this->associations as $asso)
            {
                $className = 'App\\Entity\\' . ucfirst($asso['table']);
                $obj = $asso['table'];
               
                $this->$obj = new $className();   
            }
        }*/
    }

    /**
     * \brief Vérifie si la connexion à la base de donnée est active.
     * @return bool Returne TRUE si la connexion à la base de données est active.
     */
    public function isConnected()
    { 
        return (bool) $this->connected;
    }

    private function select($fields)
    {
        if(is_string($fields))
            $select = 'SELECT ' . $fields;
        else if(is_array($fields))
            $select = 'SELECT ' . implode(', ', $fields);

        return sprintf('%s FROM `%s`', $select, $this->table);
    }

    private function contain(array $array)
    {
        $joins = [];
        foreach ($array as $asso) {

            $setting = $this->associations[$asso];

            $join = sprintf('%s JOIN $2 AS $3 ON $1.%s = $3.%s', $setting['join'], $setting['foreignKey'], $setting['primaryKey']);
            $join = str_replace('$3', ucfirst( $setting['table']), $join);
            $join = str_replace('$1', $this->table, $join);
            $join = str_replace('$2', $setting['table'], $join);
            $joins[] = $join;
        }

        return implode(' ', $joins);
    }

    private function join(array $array)
    {
        $joins = [];
        foreach ($array as $asso) {
            $join = sprintf('%s JOIN $2 ON $1.%s = $2.%s', $asso['join'], $asso['foreignKey'], $asso['primaryKey']);
            $join = str_replace('$1', $this->table, $join);
            $join = str_replace('$2', $asso['table'], $join);
            $joins[] = $join;
        }

        return implode(' ', $joins);
    }

    private function conditions(array $array)
    {
        $conditions = [];
        $exclude = ['=', '!=', '=', '>', '<', ' '];

        foreach ($array as $field => $value) 
        {
            $f = FALSE;
            foreach($exclude as $e)
            {
                if(strpos($e, $field))
                {
                    $f = TRUE;
                    break;
                }
                
                    
            }

            if(!$f)
            $field .= ' = ';
            

            //$f = str_replace($exclude, '', $field);
            //if (in_array($this->fields[$f], ['string', 'date', 'datetime']))
            if(is_string($value))
                $conditions[] = sprintf('%s "%s"', $field, $value);
            else
                $conditions[] = sprintf('%s %d', $field, $value);
            
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    private function order(array $array)
    {
        $order = [];
        foreach ($array as $field => $suffix)
            $order[] = $field . ' ' . $suffix;

        return sprintf(' ORDER BY %s', implode(', ', $order));
    }

    private function limit($value)
    {
        if(is_array($value))
        {
            return sprintf(' LIMIT %s', implode(', ', $value));
        }
        
        return ' LIMIT ' . $value;
    }

    private function addSQL(string $sql)
    {
        $this->sql[] = $sql;
    }

    private function type(array $entity): ?array
    {
        if(empty($entity))
        return NULL;

        foreach($this->fields as $key => $value)
        {
            if(isset($entity[$key]))
            {
                if($value === 'integer')
                    $entity[$key] = (int) $entity[$key];
                else if($value === 'decimal')
                    $entity[$key] = (float) $entity[$key];
            }
        }

        return $entity;
    }

    /**
     * \brief
     * Récupère le premier enregistrement de la table.
     * @param $args est un tableau contenant différents arguments facultatifs pour préparer la requête.
     * Les clés pouvant être utilisées sont 'select', 'contain', 'join', 'conditions' et 'order'.\n
     * <b>select</b> Tableau ou une chaîne de caractères indiquant les champs à inclure (['user_id', 'user_nickname', 'user_password']).\n
     * Par défaut à '*'\n
     * <b>contain</b> Tableau indiquant les associations de l'entité à inclure dans la requête (['Roles', 'Reviews']).\n
     * Par défaut à FALSE\n
     * <b>join</b> Tableau indiquant toutes les jointures à inclure dans la requête.\n
     * Par défaut à FALSE\n 
     * <b>conditions</b> Tableau indiquant les conditions de la requête (['user_nickname = ' => 'demo']).\n
     * Par défaut à FALSE\n
     * <b>order</b> Tableau avec le nom des différents champs pour ordonner la requête (['user_nickname' => 'DESC']).\n
     * Par défaut à FALSE
     * @return array Tableau contenant les champs sélectionnés de l'entité. NULL si aucun résultat.
     * @warning Les fonctions de rappels beforeFind() et afterFind() sont appelées pour chaque requête.
     * @see beforeFind()
     * @see afterFind()
     */
    public function findFirst(array $args = [])
    {
        $default = [
            'select'        => '*',
            'contain'       => FALSE,
            'join'          => FALSE,
            'conditions'    => FALSE,
            'order'         => FALSE,
        ];

        $args = array_merge($default, $this->beforeFind($args));

        $sql = $this->select($args['select']);

        if(!empty($args['contain']))
            $sql .= $this->contain($args['contain']);
            
        if(!empty($args['join']))
            $sql .= $this->join($args['join']);
        
        if(!empty($args['conditions']))
            $sql .= $this->conditions($args['conditions']);
           
        if(!empty($args['order']))
            $sql .= $this->order($args['order']);
        
        $this->addSQL($sql);

        $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if(!empty($result))
        {
            $result = $this->type($result);
            $result = $this->afterFind($result);
        }
            

        return $result;
    }

    /**
     * \brief
     * Récupère le premier enregistrement de la table avec sa clé primaire.
     * @param $args est un tableau contenant différents arguments facultatifs pour préparer la requête.
     * Les clés pouvant être utilisées sont 'select', 'contain', 'join', 'conditions' et 'order'.\n
     * * <b>select</b> doit contenir un tableau ou une chaîne de caractères indiquant les champs à inclure (['user_id', 'user_nickname', 'user_password']).\n
     * Par défaut à '*'\n
     * * <b>contain</b> doit contenir un tableau indiquant les associations de l'entité à inclure dans la requête (['Roles', 'Reviews']).\n
     * Par défaut à FALSE\n
     * * <b>join</b> doit contenir un tableau indiquant toutes les jointures à inclure dans la requête.\n
     * Par défaut à FALSE\n 
     * * <b>conditions</b> doit contenir un tableau indiquant les conditions de la requête (['user_nickname = ' => 'demo']).\n
     * Par défaut à FALSE\n
     * * <b>order</b> doit contenir un tableau avec le nom des différents champs pour ordonner la requête (['user_nickname' => 'DESC']).\n
     * Par défaut à FALSE
     * @return array Tableau contenant les champs sélectionnés de l'entité. NULL si aucun résultat.
     * @warning Les fonctions de rappels beforeFind() et afterFind() sont appelées pour chaque requête.
     * @see beforeFind()
     * @see afterFind()
     */
    public function findById($id, $args = [])
    {
        $condition = [$this->primaryKey => $id];
        
        if(isset($args['conditions']))
            $args['conditions'] = array_merge($condition, $args['conditions']);
        else $args['conditions'] = $condition;

        return $this->findFirst($args);
    }

    /**
     * \brief
     * Retourne un tableau contenant plusieurs enregistrements de la table.
     * @param $args est un tableau contenant différents arguments facultatifs pour préparer la requête.
     * Les clés pouvant être utilisées sont 'select', 'contain', 'join', 'conditions', 'order' et 'limit :\n
     * <b>select</b> doit contenir un tableau ou une chaîne de caractères indiquant les champs à inclure (['user_id', 'user_nickname', 'user_password']).\n
     * Par défaut à '*'\n
     * <b>contain</b> doit contenir un tableau indiquant les associations de l'entité à inclure dans la requête (['Roles', 'Reviews']).\n
     * Par défaut à FALSE\n
     * <b>join</b> doit contenir un tableau indiquant toutes les jointures à inclure dans la requête.\n
     * Par défaut à FALSE\n 
     * <b>conditions</b> doit contenir un tableau indiquant les conditions de la requête (['user_nickname = ' => 'demo']).\n
     * Par défaut à FALSE\n
     * <b>order</b> doit contenir un tableau avec le nom des différents champs pour ordonner la requête (['user_nickname' => 'DESC']).\n
     * Par défaut à FALSE\n
     * <b>limit</b> peut contenir un entier ou un tableau pour limiter le nombre d'enregistrements à récupérer avec un offset ([10, 5]).\n
     * Par défaut à FALSE
     * @return array Tableau contenant les entités et leurs champs sélectionnés. NULL si aucune entité trouvées.
     * @warning Les fonctions de rappels beforeFind() et afterFind() sont appelées pour chaque requête.
     * @see beforeFind()
     * @see afterFind()
     */
    public function findAll(array $args = [])
    {
        $default = [
            'select'        => '*',
            'limit'         => FALSE,
            'condition'     => FALSE,
            'order'         => FALSE,
            'contain'       => FALSE,
            'join'          => FALSE,
        ];

        $args = array_merge($default, $this->beforeFind($args));

        $sql = $this->select($args['select']);

        if(!empty($args['contain']))
            $sql .= $this->contain($args['contain']);
        
        if(!empty($args['join']))
            $sql .= $this->join($args['join']);
        
        if(!empty($args['conditions']))
            $sql .= $this->conditions($args['conditions']);
           
        if(!empty($args['order']))
            $sql .= $this->order($args['order']);
        
        if(!empty($args['limit']))
            $sql .= $this->limit($args['limit']);

        
        try {
            $query = $this->db->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_OBJ);
        }
        catch (Exception $e) {
            include TEMPLATE_DIR . DS . 'Layouts' . DS . 'error.php';
            die();
        }
        

        $this->addSQL($sql);

        if(!empty($result))
        {
            foreach($result as $key => $entity)
            {
                $result[$key] = $this->afterFind($entity);
                
                if(!empty($args['contain']))
                {
                    foreach($this->associations as $asso)
                    {
                        if(in_array(ucfirst($asso['table']), $args['contain']))
                        {
                            
                            $className = 'App\\Entity\\' . ucfirst($asso['table']);
                            $className = new $className;
                            $result[$key] = $className->afterFind($result[$key]);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * \brief
     * Récupère les enregistrements sous forme de liste clé / valeur.
     * Les fonctions de rappels beforeFind() et afterFind() ne sont pas appelées durant la requête.
     * @param array $args Tableau contenant les champs à utiliser pour la clé et la valeur.
     * Par défaut 'key' utilise la clé primaire (user_id) et 'field' la valeur primaire (user_nickname).
     * Comme pour les requêtes findAll() et findFirst(), il est possible de trier, limiter et conditionner les enregistrements.
     * @return array Tableau contenant les enregistrements sous forme de liste
     * @see findAll()
     * @see findFirst()
     */
    public function findList(array $args = [])
    {   
        
        $result = [];
        $default = [
            'key'           => $this->primaryKey,
            'field'         => $this->primaryValue,
            'condition'     => FALSE,
            'order'         => FALSE,
            'limit'         => FALSE,
        ];

        $args = array_merge($default, $args);
        

        $sql = sprintf('SELECT %s, %s FROM `%s`', $args['key'], $args['field'], $this->table);

        if(!empty($args['conditions']))
            $sql .= $this->conditions($args['conditions']);
           
        if(!empty($args['order']))
            $sql .= $this->order($args['order']);
        
        if(!empty($args['limit']))
            $sql .= $this->limit($args['limit']);

        $rows = $this->db->query($sql);
        $this->addSQL($sql);
        
        if(!empty($rows))
        {
            foreach($rows as $row)
            $result[$row[$args['key']]] = $row[$args['field']];   
        }

        return $result;
    }

    public function count(array $args = []): int
    {
        $count = 0;

        $default = [
            'condition'     => FALSE,
        ];

        $args = array_merge($default, $this->beforeFind($args));
        $args['select'] = 'COUNT(*)';

        $sql = $this->select($args['select']);

        if(!empty($args['conditions']))
            $sql .= $this->conditions($args['conditions']);
        
        $query = $this->db->prepare($sql);
        $query->execute();
        
        $result = $query->fetch();

        if(!empty($result))
        $score = (int) $result[0];

        $this->addSQL($sql);

        return $score;

    }

    public function beforeValidate(array $entity): array
    {
        return $entity;
    }

    /**
     * @brief Fonction de rappel executée avant l'enregistrement d'une entité
     * @param array $entity Tableau contenant les champs de l'entité à sauvegarder.
     * @return array Tableau contenant les champs de l'entité à sauvegarder.
     * @see afterSave()
     * @see save()
     */
    public function beforeSave(array $entity)
    {
        return $entity;
    }

    /**
     * @brief Fonction de rappel executée après l'enregistrement d'une entité
     * @param array $entity Tableau contenant les champs de l'entité à sauvegarder.
     * @return array Tableau contenant les champs de l'entité à sauvegarder.
     * @warning La fonction de rappel n'est pas executée si la sauvegarde de l'entité n'a pas aboutie.
     * @see beforeSave()
     * @see save();
     */
    public function afterSave(array $entity)
    {
        return $entity;
    }

    private function update(array $entity)
    {
        $fields = [];
        foreach($this->fields as $field => $type)
        {
            if(isset($entity[$field]) && $field !== $this->primaryKey)
            {
                if($type === 'string' || $type === 'datetime')
                $fields[] = sprintf('`%s` = "%s"', $field, $entity[$field]);
                else
                $fields[] = sprintf('`%s` = %d', $field, $entity[$field]);
            }
        }

        $fields = implode(', ', $fields);
       
        $sql = sprintf('UPDATE `$table` SET %s WHERE `$table`.`%s` = %d;', $fields, $this->primaryKey, $entity[$this->primaryKey]);
        $sql = str_replace('$table', $this->table, $sql);
        $this->sql[] = $sql;

        $result = $this->db->prepare($sql)->execute();

        if(!empty($result))
            $result = $this->afterSave($entity);

        return $result;
        
    }

    private function insert(array $entity): bool
    {
        $fields = [];
        $values = [];
        foreach ($this->fields as $field => $type) {
            $fields[] = '`' . $field . '`';

            if (empty($entity[$field]))
                $values[] = 'NULL';
            //else if ($type === 'string' || $type === 'datetime' || $type === 'date')
            else if(is_string($entity[$field]))
                $values[] = sprintf('"%s"', $entity[$field]);
            else
                $values[] = $entity[$field];
        }

        $fields = implode(', ', $fields);
        $values = implode(', ', $values);

        $sql = sprintf('INSERT INTO `$table` (%s) VALUES (%s);', $fields, $values);
        $sql = str_replace('$table', $this->table, $sql);
        $this->sql[] = $sql;

        //var_dump($sql); die();
     
        return $this->db->prepare($sql)->execute();
    }

    /**
     * @brief Enregistre une entité dans la table
     * @param $entity Tableau contenant les champs de l'entité à sauvegarder.
     * @return boolean TRUE si la requête SQL a été executée avec succès.
     * @warning Les fonctions de rappels beforeSave() et afterSave() sont executées pour chaque requête SQL.
     * @see beforeSave()
     * @see afterSave()
     */
    public function save(array $entity)
    {
        $entity = $this->type($entity);
        $entity = $this->beforeValidate($entity);
        $this->validation->validate($entity, $this->rules);
        
        if (!$this->validation->errorCount) 
        {
            $entity = $this->beforeSave($entity);
            if (empty($entity[$this->primaryKey]))
                return $this->insert($entity);

            if ($this->update($entity)) { 
                $this->afterSave($entity);
                return TRUE;
            }
        }

        $_SESSION['errors'] = $this->validation->errors;

        return FALSE;
    }

    /**
     * \brief
     * Fonction de rappel appelée avant d'exécuter la requête SQL.
     * @param $args est un tableau contenant différents arguments facultatifs pour préparer la requête qui doit être retourné à la fin de la fonction.
     * @return array Tableau contenant les champs sélectionnées de l'entité.
     * @see findAll()
     * @see findFirst()
     * @see findById()
     * @see afterFind()
     */
    public function beforeFind(array $args = [])
    {
        return $args;
    }
    /**
     * \brief
     * Fonction de rappel appelée après l'exécution de la requête SQL.
     * @param $entity est un tableau contenant les champs sélectionnées de l'entité.
     * @return array Tableau contenant les champs sélectionnées de l'entité.
     * @see findAll()
     * @see findFirst()
     * @see findById()
     * @see beforeFind()
     */
    public function afterFind(stdClass $entity): stdClass
    {
        return $entity;
    }

     /**
     * @brief Fonction de rappel appelée avant la suppression d'un enregistrement.
     * @param $entity Tableau contenant les champs sélectionnées de l'entité.
     * @return stdClass Tableau contenant au minimum la clé primaire de l'entité.
     * @see afterDelete()
     * @see delete()
     */
    public function beforeDelete(stdClass $entity)
    {
        return $entity;
    }
    
    /**
     * @brief Fonction de rappel appelée après la suppression d'un enregistrement.
     * @param $entity Tableau contenant les champs sélectionnées de l'entité.
     * @return stdClass Tableau contenant au minimum la clé primaire de l'entité.
     * @see afterDelete()
     * @see delete()
     */
    public function afterDelete(stdClass $entity)
    {
        return $entity;
    }

    /**
     * @brief Retourne la dernière requête SQL executée.
     * @return string Chaîne de caractère contenant la dernière requête SQL executée. NULL si aucune requête n'a été executée.
     * @see findAll()
     * @see findFirst()
     * @see findById()
     * @see save()
     * @see delete()
     */
    public function lastQuery(): ?string
    {
        return empty($this->sql) ? NULL : end($this->sql);
    }

    /**
     * \brief
     * Supprime un enregistrement dans la table en utilisant la clé primaire.
     * Les fonctions de rappels beforeDelete() et afterDelete() sont appelées à chaque requête SQL.
     * @param $entity est un tableau contenant au minimum la clé primaire de l'entité. 
     * @return bool TRUE si la requête SQL a été exécutée avec succès.
     * @see beforeDelete()
     * @see afterDelete()
     */
    public function delete(array $entity): bool
    {
        $entity = $this->beforeDelete($entity);

        $sql = sprintf('DELETE FROM %s WHERE %s = %d', $this->table, $this->primaryKey, $entity[$this->primaryKey]);
     
        if($this->db->prepare($sql)->execute())
        {
            $this->afterDelete($entity);
            $this->addSQL($sql);
            return TRUE;
        }

        return FALSE;
    }
}