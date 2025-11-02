<?php
require_once 'BaseDao.php';

class OrdersDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('orders');
    }
    
    public function get_all() {
        $query = "SELECT o.*, 
                         u.first_name, u.last_name, u.email,
                         pm.method_name as payment_method
                  FROM {$this->table_name} o
                  JOIN users u ON o.user_id = u.id
                  JOIN payment_methods pm ON o.payment_method_id = pm.id
                  ORDER BY o.order_date DESC";
        return $this->query($query, []);
    }
    
    public function get_by_id($id) {
        $query = "SELECT o.*, 
                         u.first_name, u.last_name, u.email,
                         pm.method_name as payment_method
                  FROM {$this->table_name} o
                  JOIN users u ON o.user_id = u.id
                  JOIN payment_methods pm ON o.payment_method_id = pm.id
                  WHERE o.id = :id";
        return $this->query_unique($query, ['id' => $id]);
    }
    
    public function get_by_user_id($user_id) {
        $query = "SELECT o.*, pm.method_name as payment_method
                  FROM {$this->table_name} o
                  JOIN payment_methods pm ON o.payment_method_id = pm.id
                  WHERE o.user_id = :user_id
                  ORDER BY o.order_date DESC";
        return $this->query($query, ['user_id' => $user_id]);
    }
    
    public function get_order_with_items($id) {
        $order = $this->get_by_id($id);
        
        if ($order) {
            $items_query = "SELECT oi.*, p.name as product_name, p.price, p.image_url
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.id
                           WHERE oi.order_id = :order_id";
            $order['items'] = $this->query($items_query, ['order_id' => $id]);
        }
        
        return $order;
    }
    
    public function get_recent_orders($limit = 10) {
        $query = "SELECT o.*, 
                         u.first_name, u.last_name,
                         pm.method_name as payment_method
                  FROM {$this->table_name} o
                  JOIN users u ON o.user_id = u.id
                  JOIN payment_methods pm ON o.payment_method_id = pm.id
                  WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  ORDER BY o.order_date DESC
                  LIMIT :limit";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function add_order($entity) {
        $entity['order_date'] = date('Y-m-d H:i:s');
        $entity['created_at'] = date('Y-m-d H:i:s');
        $entity['updated_at'] = date('Y-m-d H:i:s');
        
        if (!isset($entity['tax_amount'])) {
            $entity['tax_amount'] = 0;
        }
        if (!isset($entity['shipping_cost'])) {
            $entity['shipping_cost'] = 0;
        }
        
        return $this->add($entity);
    }
    
    public function update_order($id, $entity) {
        $entity['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($entity, $id);
    }
    
    public function update_shipping_status($id, $shipped_date = null, $delivered_date = null) {
        $entity = ['updated_at' => date('Y-m-d H:i:s')];
        
        if ($shipped_date !== null) {
            $entity['shipped_date'] = $shipped_date;
        }
        if ($delivered_date !== null) {
            $entity['delivered_date'] = $delivered_date;
        }
        
        return $this->update($entity, $id);
    }
    
    // deletes order items
    public function delete($id) {
        // delete order items
        $stmt = $this->connection->prepare("DELETE FROM order_items WHERE order_id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        // delete order
        parent::delete($id);
    }
}