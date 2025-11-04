<?php
require_once 'BaseDao.php';

class PaymentMethodsDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('payment_methods');
    }
    
    public function get_all() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY method_name";
        return $this->query($query, []);
    }
    
    public function get_active() {
        $query = "SELECT * FROM {$this->table_name} WHERE is_active = 1 ORDER BY method_name";
        return $this->query($query, []);
    }
    
    public function get_by_id($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    public function get_with_usage_count($id) {
        $query = "SELECT pm.*, COUNT(o.id) as usage_count 
                  FROM {$this->table_name} pm 
                  JOIN orders o ON pm.id = o.payment_method_id 
                  WHERE pm.id = :id 
                  GROUP BY pm.id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    public function add_payment_method($entity) {
        if (!isset($entity['is_active'])) {
            $entity['is_active'] = 1;
        }
        $entity['created_at'] = date('Y-m-d H:i:s');
        $entity['updated_at'] = date('Y-m-d H:i:s');
        return $this->add($entity);
    }
    
    public function update_payment_method($id, $entity) {
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