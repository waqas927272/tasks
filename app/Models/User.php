<?php

namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'role'];
    
    public function findByEmail($email) {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE email = ?", [$email]);
    }
    
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function create($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return parent::create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        return parent::update($id, $data);
    }
    
    public function getClients() {
        return $this->where('role', 'client');
    }
    
    public function getCSMs() {
        return $this->where('role', 'csm');
    }
    
    public function tasks() {
        $taskModel = new Task();
        if ($this->role === 'client') {
            return $taskModel->where('client_id', $this->id);
        } elseif ($this->role === 'csm') {
            return $taskModel->where('csm_id', $this->id);
        }
        return $taskModel->all();
    }
}