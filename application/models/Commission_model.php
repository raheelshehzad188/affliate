<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Commission_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Affiliate_model');
    }

    // Create commission
    public function create($data) {
        return $this->db->insert('commissions', $data) ? $this->db->insert_id() : false;
    }

    // Process commissions by lead (multi-level)
    public function process_commissions_by_lead($lead_id, $total_sale) {
        $this->load->model('Lead_model');
        $lead = $this->Lead_model->get_by_id($lead_id);
        
        if (!$lead || !$lead->affiliate_id) {
            return false;
        }

        $commission_structure = [
            ['percent' => 10, 'level' => 1],
            ['percent' => 5, 'level' => 2],
            ['percent' => 2, 'level' => 3],
            ['percent' => 1, 'level' => 4],
        ];

        $current_affiliate_id = $lead->affiliate_id;
        
        // Check if lead is confirmed - if yes, commission status should be 'confirmed'
        $commission_status = ($lead->status === 'confirmed') ? 'confirmed' : 'pending';
        
        foreach ($commission_structure as $level_data) {
            if (!$current_affiliate_id) break;
            
            $affiliate = $this->Affiliate_model->get_by_id($current_affiliate_id);
            if (!$affiliate) break;
            
            $commission_amount = round(($total_sale * $level_data['percent']) / 100, 2);
            
            $this->create([
                'affiliate_id' => $current_affiliate_id,
                'lead_id' => $lead_id,
                'total_sale' => $total_sale,
                'commission_amount' => $commission_amount,
                'commission_percent' => $level_data['percent'],
                'level' => $level_data['level'],
                'status' => $commission_status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Get parent for next level
            $parent = $this->Affiliate_model->get_parent($current_affiliate_id);
            $current_affiliate_id = $parent ? $parent->id : null;
        }
        
        return true;
    }
    
    // Update commission status when lead is confirmed
    public function update_status_by_lead($lead_id, $status = 'confirmed') {
        return $this->db->where('lead_id', $lead_id)
                        ->update('commissions', ['status' => $status]);
    }

    // Get commissions by affiliate
    public function get_by_affiliate($affiliate_id, $filters = []) {
        $this->db->where('affiliate_id', $affiliate_id);
        
        if (isset($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        
        return $this->db->order_by('created_at', 'DESC')->get('commissions')->result();
    }

    // Get total commission
    public function get_total_by_affiliate($affiliate_id, $from_date = null, $to_date = null) {
        $this->db->select_sum('commission_amount');
        $this->db->where('affiliate_id', $affiliate_id);
        $this->db->where('status', 'pending');
        
        if ($from_date && $to_date) {
            $this->db->where('created_at >=', $from_date . ' 00:00:00');
            $this->db->where('created_at <=', $to_date . ' 23:59:59');
        }
        
        $result = $this->db->get('commissions')->row();
        return $result->commission_amount ? $result->commission_amount : 0;
    }

    // Get all commissions with filters
    public function get_all($filters = [], $limit = null, $offset = null) {
        if (isset($filters['affiliate_id'])) {
            $this->db->where('affiliate_id', $filters['affiliate_id']);
        }
        if (isset($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->order_by('created_at', 'DESC')->get('commissions')->result();
    }
}

