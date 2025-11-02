<?php
require_once 'BaseDao.php';

class CategoriesDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('categories');
    }
    
   
    public function get_all() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY name";
        return $this->query($query, []);
    }
    

    public function get_by_id($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    
    public function get_with_product_count($id) {
        $query = "SELECT c.*, COUNT(p.id) as product_count 
                  FROM {$this->table_name} c 
                  JOIN products p ON c.id = p.category_id 
                  WHERE c.id = :id 
                  GROUP BY c.id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    public function add_category($entity) {
        $entity['created_at'] = date('Y-m-d H:i:s');
        $entity['updated_at'] = date('Y-m-d H:i:s');
        return $this->add($entity);
    }
    
    public function update_category($id, $entity) {
        $entity['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($entity, $id);
    }
}