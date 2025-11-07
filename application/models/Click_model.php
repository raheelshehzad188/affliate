<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Click_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Record affiliate click
    public function record($affiliate_id, $ip_address = null, $referrer = null) {
        // Get country from IP
        $country = $this->get_country_from_ip($ip_address ?: $this->input->ip_address());
        
        $data = [
            'affiliate_id' => $affiliate_id,
            'ip_address' => $ip_address ?: $this->input->ip_address(),
            'country' => $country,
            'referrer_url' => $referrer,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('affiliate_clicks', $data) ? $this->db->insert_id() : false;
    }

    // Get country from IP
    private function get_country_from_ip($ip) {
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}");
            if ($response) {
                $data = json_decode($response);
                if ($data && $data->status === 'success') {
                    return $data->country;
                }
            }
        } catch (Exception $e) {
            // Silent fail
        }
        return '';
    }

    // Get clicks by affiliate
    public function get_by_affiliate($affiliate_id, $from_date = null, $to_date = null) {
        $this->db->where('affiliate_id', $affiliate_id);
        if ($from_date && $to_date) {
            $this->db->where('created_at >=', $from_date . ' 00:00:00');
            $this->db->where('created_at <=', $to_date . ' 23:59:59');
        }
        return $this->db->order_by('created_at', 'DESC')->get('affiliate_clicks')->result();
    }

    // Count clicks
    public function count($affiliate_id = null, $from_date = null, $to_date = null) {
        if ($affiliate_id) {
            $this->db->where('affiliate_id', $affiliate_id);
        }
        if ($from_date && $to_date) {
            $this->db->where('created_at >=', $from_date . ' 00:00:00');
            $this->db->where('created_at <=', $to_date . ' 23:59:59');
        }
        return $this->db->count_all_results('affiliate_clicks');
    }
    
    // Count clicks by date range
    public function count_by_date_range($affiliate_id, $from_date, $to_date) {
        $this->db->where('affiliate_id', $affiliate_id);
        $this->db->where('DATE(created_at) >=', $from_date);
        $this->db->where('DATE(created_at) <=', $to_date);
        return $this->db->count_all_results('affiliate_clicks');
    }
}

