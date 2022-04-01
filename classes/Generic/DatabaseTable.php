<?php
namespace Generic;

class DatabaseTable {
    private $table;
    private $pdo;
    private $primaryKey;

    public function __construct($pdo, $table, $primaryKey){
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    private function query($sql, $parameters = []){
        $query = $this->pdo->prepare($sql);
        $query->execute($parameters);
        return $query;
    }
    private function processDate($fields){
        foreach($fields as $key => $value){
            if ($value instanceof \DateTime){
                $fields[$key] = $value->format('Y-m-d');
            }
        }
        return $fields;
    }

    public function insert($fields){
        $query = 'INSERT INTO `'. $this->table . '` (';
        foreach($fields as $key => $value){
            $query .= '`' . $key . '` ,';
        }

        $query = rtrim($query, ",");
        $query .= ") VALUES (";

        foreach($fields as $key => $value){
            $query .= ' :' . $key . ' ,';
        }
        $query = rtrim($query, ",");
        
        $query .= ")";
        $fields = $this->processDate($fields);
        $this->query($query, $fields);
    }

    private function update($fields){
        $query = 'UPDATE '. $this->table . ' SET ';
        foreach($fields as $key => $value){
            $query .= '`' . $key . '` = :' . $key . ',';
        }
        $query = rtrim(",", $query);
        $query .= ' WHERE ' . $this->primaryKey . ' = :primaryKey';
        $fields['id'] =  $this->primaryKey;
        $fields = $this->processDate($fields);
        $this->query($query, $fields);
    }
    public function findAll(){
        $query = isset($column) ? 'SELECT  * FROM `' . $this->table .'`' : 'SELECT * FROM `' . $this->table .'`';
        $rows = $this->query($query);
        return $rows->fetchAll();
    }
    public function findById($column, $value){
        $query = 'SELECT * FROM `' . $this->table . '` WHERE `' . $column . '` = :value';
        $parameters = [':value' => $value];
        $result = $this->query($query, $parameters);
        $row = $result->fetchAll();
        return $row;
    }

    public function find($column, $value){
        $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $column . ' = :value';
        $parameters = ['value' => $value];
        $result =  $this->query($query, $parameters);
        return $result->fetchAll();
    }

    public function findByContraint($table1, $table2, $table3, $table1_column1, $table1_column2, $table1_column3, $table1_column4, $table3_column){
        $sql = ' SELECT DISTINCT ' . $table1 . '.'.$table1_column1 . ','. $table1. '.'.$table1_column2 . ', ' 
        . $table1. '.'.$table1_column3 . ', ' . $table1. '.'.$table1_column4 . ' FROM '
         . $table1 . ' INNER JOIN ' . $table2 .  ' ON ' . $table1. '.'.$table1_column1 .' = '. $table2. 
         '.'.$table1_column1 .' LEFT JOIN ' . $table3 . ' ON ' . $table2 . '.' .$table1_column1 .'='. $table3. 
         '.'.$table3_column .  ' WHERE ' . $table3 . '.' .$table3_column . ' IS NULL';
        $result = $this->query($sql);
        return $result->fetchAll();
    }
    public function findByConstraints($table1, $table2, $column1, $column2, $column3){
        $sql = ' SELECT ' . $table1.'.'.$column1. ', ' . $table1.'.'.$column3. ' FROM '.$table1 . ' LEFT JOIN ' . $table2 . ' ON ' 
        . $table1.'.'.$column1 . '='.$table2.'.'.$column1 
        . ' WHERE ' . $table2 . '.'.$column2 . ' IS NULL ORDER BY ' . $column3 . ' ASC';
        $result = $this->query($sql);
        return $result->fetchAll();
    }
    public function findByParam($table1, $table2, $table3, $table_column0, $table_column, $table2_column, $table_column2){
        $sql = ' SELECT '. $table1.'.'.$table_column . ', ' . $table3.'.'.$table_column0 . ','. $table3 . '.' . $table_column2 . ' FROM ' . $table1  
        . ' INNER JOIN ' . $table2 . ' ON ' . $table1. '.'. $table_column . ' = ' . $table2 . '.'. $table_column 
        . ' LEFT JOIN ' . $table3 . ' ON ' . $table2 . '.'.$table_column . ' = ' . $table3 . '.'.$table_column 
        . ' WHERE ' . $table2.'.'. $table_column . ' = ' . $table2_column;
        $result = $this->query($sql);
        return $result->fetchAll();
    }
    public function findByParams($table1, $table2, $table3, $table4, $column1, $column2, $column3, $column4, $column5, $column6, $unique_column){
        $sql = ' SELECT DISTINCT ' . $table1.'.'.$column1 . ', ' . $table1.'.'.$column2 . ', '.$table1.'.'.$column3.', '
        .$table1.'.'.$column4 . ','. $table2.'.'.$column5 . ' FROM ' . $table1 . ' INNER JOIN ' . $table2 . ' ON ' 
        . $table1.'.'.$column1 . '='.$table2.'.'.$column1 . ' LEFT JOIN ' . $table3 .' ON ' . $table1.'.'.$column1 
        . '=' . $table3.'.'.$column1 . ' INNER JOIN ' . $table4 . ' ON '. $table1.'.'. $column6 .'=' . $table4.'.'.$column6 
        . ' WHERE ' . $table3.'.'.$column1 . ' IS NULL AND '  . $table4 . '.'.$column6 . '='.$unique_column . ' ORDER BY ' . $column2 . ' ASC ';
        $result = $this->query($sql);
        return $result->fetchAll();
    }

    public function total($column){
        $sql = 'SELECT  COUNT(DISTINCT $column) FROM `' . $this->table . '`';
        $result = $this->query($sql);
        $row = $result->fetch();
        return $row[0];
    }

    public function save($record){
        var_dump($record[$this->primaryKey]);
        try {
            if ($record[$this->primaryKey] == ''){
                $record[$this->primaryKey] = null;
            }
            $this->insert($record);
        } catch (\PDOException $e) {
            // $this->update($record);
        }
    }
    public function delete($value){
        $query = 'DELETE FROM `'. $this->table . '` WHERE `'. $this->primaryKey . '`= :value';
        $parameters = [':value' => $value];
        $this->query($query, $parameters);
    }
}