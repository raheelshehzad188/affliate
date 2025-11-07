<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Create new affiliate
    public function create($data) {
        $data['password'] = md5($data['password']);
        $data['verification_token'] = md5($data['email'] . time());
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Generate unique slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->generate_unique_slug($data['username'], $data['full_name']);
        }
        
        return $this->db->insert('affiliates', $data) ? $this->db->insert_id() : false;
    }
    
    // Generate unique slug
    private function generate_unique_slug($username, $full_name = '') {
        // Use username as base, or full name if available
        $base = !empty($full_name) ? $full_name : $username;
        
        // Convert to slug format
        $slug = strtolower(trim($base));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure minimum length
        if (strlen($slug) < 3) {
            $slug = $username . '-' . substr(md5(time()), 0, 5);
        }
        
        // Check if slug exists, if yes add number
        $original_slug = $slug;
        $counter = 1;
        while ($this->slug_exists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    // Check if slug exists
    private function slug_exists($slug) {
        $result = $this->db->where('slug', $slug)->get('affiliates');
        return $result->num_rows() > 0;
    }
    
    // Get affiliate by slug
    public function get_by_slug($slug) {
        return $this->db->where('slug', $slug)->get('affiliates')->row();
    }

    // Get affiliate by ID
    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('affiliates')->row();
    }

    // Get affiliate by email
    public function get_by_email($email) {
        return $this->db->where('email', $email)->get('affiliates')->row();
    }

    // Get affiliate by username
    public function get_by_username($username) {
        return $this->db
            ->where('(username = "' . $this->db->escape_str($username) . '" OR email = "' . $this->db->escape_str($username) . '")', null, false)
            ->get('affiliates')
            ->row();
    }

    // Verify affiliate login
    public function verify_login($username, $password) {
        $affiliate = $this->get_by_username($username);
        if ($affiliate && md5($password) === $affiliate->password) {
            if ($affiliate->status === 'active' || $affiliate->email_verified == 1) {
                return $affiliate;
            }
        }
        return false;
    }

    // Update affiliate
    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Remove any null or empty string values that might cause issues
        $data = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        
        if (empty($data)) {
            return false;
        }
        
        $this->db->where('id', $id);
        $result = $this->db->update('affiliates', $data);
        
        // Log error if update fails
        if (!$result) {
            $error = $this->db->error();
            log_message('error', 'Affiliate update failed: ' . print_r($error, true));
        }
        
        return $result;
    }

    // Verify email token
    public function verify_email_token($token) {
        $affiliate = $this->db->where('verification_token', $token)->get('affiliates')->row();
        if ($affiliate) {
            $this->db->where('id', $affiliate->id)->update('affiliates', [
                'email_verified' => 1,
                'verification_token' => NULL,
                'status' => 'active'
            ]);
            return true;
        }
        return false;
    }

    // Get all affiliates with filters
    public function get_all($filters = []) {
        if (isset($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (isset($filters['search'])) {
            $this->db->group_start();
            $this->db->like('full_name', $filters['search']);
            $this->db->or_like('email', $filters['search']);
            $this->db->or_like('username', $filters['search']);
            $this->db->group_end();
        }
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $this->db->where('created_at >=', $filters['from_date'] . ' 00:00:00');
            $this->db->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        return $this->db->order_by('created_at', 'DESC')->get('affiliates')->result();
    }

    // Get referral tree
    public function get_referrals($affiliate_id, $level = 1) {
        return $this->db->where('referred_by', $affiliate_id)->get('affiliates')->result();
    }

    // Get parent affiliate
    public function get_parent($affiliate_id) {
        $affiliate = $this->get_by_id($affiliate_id);
        return $affiliate && $affiliate->referred_by ? $this->get_by_id($affiliate->referred_by) : null;
    }

    // Get stats for affiliate
    public function get_stats($affiliate_id, $from_date = null, $to_date = null) {
        $this->load->model('Lead_model');
        $this->load->model('Commission_model');
        
        $stats = [
            'clicks' => $this->get_clicks_count($affiliate_id, $from_date, $to_date),
            'total_leads' => $this->Lead_model->count_by_affiliate($affiliate_id, $from_date, $to_date),
            'pending_leads' => $this->Lead_model->count_by_affiliate($affiliate_id, $from_date, $to_date, 'pending'),
            'confirmed_leads' => $this->Lead_model->count_by_affiliate($affiliate_id, $from_date, $to_date, 'confirmed'),
            'total_commission' => $this->Commission_model->get_total_by_affiliate($affiliate_id, $from_date, $to_date),
            'level1_referrals' => count($this->get_referrals($affiliate_id)),
        ];
        
        return $stats;
    }

    // Get clicks count
    public function get_clicks_count($affiliate_id, $from_date = null, $to_date = null) {
        $this->db->where('affiliate_id', $affiliate_id);
        if ($from_date && $to_date) {
            $this->db->where('created_at >=', $from_date . ' 00:00:00');
            $this->db->where('created_at <=', $to_date . ' 23:59:59');
        }
        return $this->db->count_all_results('affiliate_clicks');
    }
    
    // Get weekly performance data for graph
    public function get_weekly_performance($affiliate_id) {
        $this->load->model(['Click_model', 'Lead_model']);
        
        $data = [];
        
        // Month start and end
        $start = new DateTime(date('Y-m-01')); // start of month
        $end = new DateTime(); // today's date
        $end->modify('+1 day');
        
        // Iterate week by week
        $week_number = 1;
        while ($start < $end) {
            // Calculate week start and end
            $week_start = clone $start;
            $week_end = clone $week_start;
            $week_end->modify('+6 days');
            if ($week_end > $end) {
                $week_end = clone $end;
            }
            
            // Fetch weekly data
            $clicks = $this->Click_model->count_by_date_range($affiliate_id, $week_start->format('Y-m-d'), $week_end->format('Y-m-d'));
            $pending = $this->Lead_model->count_by_affiliate($affiliate_id, $week_start->format('Y-m-d'), $week_end->format('Y-m-d'), 'pending');
            $confirmed = $this->Lead_model->count_by_affiliate($affiliate_id, $week_start->format('Y-m-d'), $week_end->format('Y-m-d'), 'confirmed');
            
            $data[] = [
                'date' => "Week $week_number",
                'clicks' => $clicks,
                'pending' => $pending,
                'confirmed' => $confirmed,
            ];
            
            // Move to next week
            $start->modify('+7 days');
            $week_number++;
        }
        
        return $data;
    }
}

