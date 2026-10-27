<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Withdraw_request_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('withdraw_requests', $data) ? $this->db->insert_id() : false;
    }

    public function get_by_id($id) {
        return $this->db->select('withdraw_requests.*, affiliates.full_name, affiliates.email')
            ->join('affiliates', 'affiliates.id = withdraw_requests.affiliate_id')
            ->where('withdraw_requests.id', $id)
            ->get('withdraw_requests')
            ->row();
    }

    public function get_by_affiliate($affiliate_id) {
        return $this->db->where('affiliate_id', $affiliate_id)
            ->order_by('created_at', 'DESC')
            ->get('withdraw_requests')
            ->result();
    }

    public function get_all($filters = []) {
        $this->db->select('withdraw_requests.*, affiliates.full_name, affiliates.email');
        $this->db->join('affiliates', 'affiliates.id = withdraw_requests.affiliate_id');
        if (isset($filters['status']) && $filters['status']) {
            $this->db->where('withdraw_requests.status', $filters['status']);
        }
        return $this->db->order_by('withdraw_requests.created_at', 'DESC')->get('withdraw_requests')->result();
    }

    public function update($id, $data) {
        if (isset($data['status']) && in_array($data['status'], ['approved', 'rejected'])) {
            $data['processed_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->where('id', $id)->update('withdraw_requests', $data);
    }

    public function has_pending($affiliate_id) {
        return $this->db->where('affiliate_id', $affiliate_id)
            ->where('status', 'pending')
            ->count_all_results('withdraw_requests') > 0;
    }
}
