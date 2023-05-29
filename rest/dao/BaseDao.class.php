<?php

require_once __DIR__.'/../env.class.php';
class BaseDao{

    private $conn;
    private $table_name;

    public function __construct($table_name){

        $this -> table_name = $table_name;
        $servername = Env::DB_HOST();
        $username = Env::DB_USERNAME();
        $password = Env::DB_PASSWORD();
        $schema = Env::DB_SCHEME();
        $port = Env::DB_PORT();

        $this->conn = new PDO("mysql:host=$servername;port=$port;dbname=$schema", $username, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function add_element($entity){
            $query = "INSERT INTO ".$this->table_name." (";
            foreach ($entity as $column => $value) {
              $query .= $column.", ";
            }
            $query = substr($query, 0, -2);
            $query .= ") VALUES (";
            foreach ($entity as $column => $value) {
              $query .= ":".$column.", ";
            }
            $query = substr($query, 0, -2);
            $query .= ")";

            $stmt= $this->conn->prepare($query);
            $stmt->execute($entity);
            $entity['id'] = $this->conn->lastInsertId();
            return $entity;
          }

      protected function query($query, $params){
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
      }

      protected function query_unique($query, $params){
        $results = $this->query($query, $params);
        return reset($results);
      }

    public function add($entity){
        return $this->add_element($entity);
    }

}
?>
