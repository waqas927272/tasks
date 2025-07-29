<?php

namespace App\Core;

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    public function where($column, $value, $operator = '=') {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        return $this->db->fetchAll($sql, [$value]);
    }
    
    public function create($data) {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        $columns = implode(', ', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->execute($sql, $fields);
        
        return $this->find($this->db->lastInsertId());
    }
    
    public function update($id, $data) {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        $set = [];
        foreach ($fields as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setString = implode(', ', $set);
        
        $fields[$this->primaryKey] = $id;
        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :{$this->primaryKey}";
        
        return $this->db->execute($sql, $fields);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    public function belongsTo($model, $foreignKey, $ownerKey = 'id') {
        $modelInstance = new $model();
        $value = $this->{$foreignKey} ?? null;
        return $value ? $modelInstance->find($value) : null;
    }
    
    public function hasMany($model, $foreignKey, $localKey = 'id') {
        $modelInstance = new $model();
        $value = $this->{$localKey} ?? null;
        return $value ? $modelInstance->where($foreignKey, $value) : [];
    }
}