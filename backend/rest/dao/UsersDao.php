<?php
require_once 'BaseDao.php';

class UsersDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('users');
    }
    
    // READ - Get all users
    public function get_all() {
        $query = "SELECT id, first_name, last_name, email FROM {$this->table_name}";
        return $this->query($query, []);
    }
    
    // READ - Get user by ID
    public function get_by_id($id) {
        $query = "SELECT id, first_name, last_name, email FROM {$this->table_name} WHERE id = :id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    // READ - Get user by email (for authentication)
    public function get_by_email($email) {
        $query = "SELECT * FROM {$this->table_name} WHERE email = :email";
        return $this->query_unique($query, ['email' => $email]);
    }
    
    // CREATE with password hashing
    public function add_user($entity) {
        if (isset($entity['password'])) {
            $entity['password'] = password_hash($entity['password'], PASSWORD_DEFAULT);
        }
        return $this->add($entity);
    }
    
    // UPDATE user profile (without password)
    public function update_profile($id, $entity) {
        return $this->update($entity, $id);
    }
    
    // UPDATE password
    public function update_password($id, $new_password) {
        $entity = [
            'password' => password_hash($new_password, PASSWORD_DEFAULT)
        ];
        return $this->update($entity, $id);
    }
}