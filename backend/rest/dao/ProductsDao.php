<?php
require_once 'BaseDao.php';

class ProductsDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('products');
    }
    
    public function get_all() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table_name} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.created_at DESC";
        return $this->query($query, []);
    }
    
    public function get_by_id($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table_name} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    public function get_by_category($category_id) {
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE category_id = :category_id AND is_active = 1 
                  ORDER BY name";
        return $this->query($query, ['category_id' => $category_id]);
    }
    
    public function get_active_products() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table_name} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1 
                  ORDER BY p.created_at DESC";
        return $this->query($query, []);
    }
    
    public function search($search_term) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table_name} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE (p.name LIKE :search OR p.description LIKE :search) 
                  AND p.is_active = 1";
        return $this->query($query, ['search' => "%$search_term%"]);
    }
    
    public function add_product($entity) {
        if (!isset($entity['is_active'])) {
            $entity['is_active'] = 1;
        }
        if (!isset($entity['rating'])) {
            $entity['rating'] = 0;
        }
        $entity['created_at'] = date('Y-m-d H:i:s');
        $entity['updated_at'] = date('Y-m-d H:i:s');
        return $this->add($entity);
    }
    
    public function update_product($id, $entity) {
        $entity['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($entity, $id);
    }
    
    public function toggle_active($id) {
        $query = "UPDATE {$this->table_name} 
                  SET is_active = NOT is_active, updated_at = :updated_at 
                  WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([
            'id' => $id,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->get_by_id($id);
    }
}