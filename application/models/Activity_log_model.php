<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Create activity log
    public function create($data) {
        // Check if table exists
        if (!$this->db->table_exists('activity_logs')) {
            return false;
        }
        
        $log_data = [
            'admin_id' => $data['admin_id'],
            'admin_name' => isset($data['admin_name']) ? $data['admin_name'] : null,
            'action_type' => $data['action_type'],
            'action_description' => $data['action_description'],
            'entity_type' => isset($data['entity_type']) ? $data['entity_type'] : null,
            'entity_id' => isset($data['entity_id']) ? $data['entity_id'] : null,
            'old_data' => isset($data['old_data']) ? json_encode($data['old_data']) : null,
            'new_data' => isset($data['new_data']) ? json_encode($data['new_data']) : null,
            'ip_address' => $this->input->ip_address()
        ];
        
        return $this->db->insert('activity_logs', $log_data) ? $this->db->insert_id() : false;
    }

    // Get all logs with filters
    public function get_all($filters = [], $limit = null, $offset = 0) {
        // Check if table exists
        if (!$this->db->table_exists('activity_logs')) {
            return [];
        }
        
        if (isset($filters['admin_id']) && !empty($filters['admin_id'])) {
            $this->db->where('admin_id', $filters['admin_id']);
        }
        
        if (isset($filters['action_type']) && !empty($filters['action_type'])) {
            $this->db->where('action_type', $filters['action_type']);
        }
        
        if (isset($filters['entity_type']) && !empty($filters['entity_type'])) {
            $this->db->where('entity_type', $filters['entity_type']);
        }
        
        if (isset($filters['from_date']) && !empty($filters['from_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        
        if (isset($filters['to_date']) && !empty($filters['to_date'])) {
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        
        $this->db->order_by('created_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get('activity_logs')->result();
    }

    // Count logs
    public function count($filters = []) {
        // Check if table exists
        if (!$this->db->table_exists('activity_logs')) {
            return 0;
        }
        
        if (isset($filters['admin_id']) && !empty($filters['admin_id'])) {
            $this->db->where('admin_id', $filters['admin_id']);
        }
        
        if (isset($filters['action_type']) && !empty($filters['action_type'])) {
            $this->db->where('action_type', $filters['action_type']);
        }
        
        if (isset($filters['entity_type']) && !empty($filters['entity_type'])) {
            $this->db->where('entity_type', $filters['entity_type']);
        }
        
        if (isset($filters['from_date']) && !empty($filters['from_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        
        if (isset($filters['to_date']) && !empty($filters['to_date'])) {
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        
        return $this->db->count_all_results('activity_logs');
    }

    // Get log by ID
    public function get_by_id($id) {
        $log = $this->db->where('id', $id)->get('activity_logs')->row();
        if ($log) {
            if ($log->old_data) {
                $log->old_data = json_decode($log->old_data, true);
            }
            if ($log->new_data) {
                $log->new_data = json_decode($log->new_data, true);
            }
        }
        return $log;
    }
}

