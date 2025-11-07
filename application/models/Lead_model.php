<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Create new lead
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('leads', $data) ? $this->db->insert_id() : false;
    }

    // Get lead by ID
    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('leads')->row();
    }

    // Get all leads with filters
    public function get_all($filters = [], $limit = null, $offset = null) {
        if (isset($filters['affiliate_id']) && !empty($filters['affiliate_id'])) {
            $this->db->where('affiliate_id', $filters['affiliate_id']);
        }
        if (isset($filters['status']) && !empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['from_date']) && !empty($filters['from_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (isset($filters['to_date']) && !empty($filters['to_date'])) {
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->order_by('created_at', 'DESC')->get('leads')->result();
    }

    // Count leads
    public function count($filters = []) {
        if (isset($filters['affiliate_id']) && !empty($filters['affiliate_id'])) {
            $this->db->where('affiliate_id', $filters['affiliate_id']);
        }
        if (isset($filters['status']) && !empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['from_date']) && !empty($filters['from_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        }
        if (isset($filters['to_date']) && !empty($filters['to_date'])) {
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        return $this->db->count_all_results('leads');
    }

    // Count by affiliate
    public function count_by_affiliate($affiliate_id, $from_date = null, $to_date = null, $status = null) {
        $this->db->where('affiliate_id', $affiliate_id);
        if ($status) {
            $this->db->where('status', $status);
        }
        if ($from_date && $to_date) {
            $this->db->where('created_at >=', $from_date . ' 00:00:00');
            $this->db->where('created_at <=', $to_date . ' 23:59:59');
        }
        return $this->db->count_all_results('leads');
    }

    // Update lead
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update('leads', $data);
    }

    // Confirm lead
    public function confirm($id, $sale_amount, $feedback) {
        return $this->update($id, [
            'status' => 'confirmed',
            'sale_amount' => $sale_amount,
            'feedback' => $feedback
        ]);
    }
}

