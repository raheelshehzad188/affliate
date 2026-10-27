<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Affiliate_model', 'Lead_model', 'Commission_model', 'Click_model', 'Settings_model', 'Withdraw_request_model']);
        $this->load->library('session');
        
        // Don't check auth for landing page
        $method = $this->uri->segment(2);
        if ($method && $method !== 'landing') {
            if (!$this->session->userdata('affiliate_id')) {
                redirect('auth/login');
            }
        }
    }

    // Affiliate Landing Page (Public)
    public function landing($slug = '') {
        if (empty($slug)) {
            show_404();
            return;
        }
        
        $affiliate = $this->Affiliate_model->get_by_slug($slug);
        
        if (!$affiliate || $affiliate->status !== 'active') {
            show_404();
            return;
        }
        
        // Track click
        $this->Click_model->record($affiliate->id);
        
        // Set cookie for lead tracking
        setcookie('affiliate_id', $affiliate->id, time() + (30 * 24 * 60 * 60), '/');
        
        // Decode discount from URL if present
        $discount_percent = null;
        $discount_code = $this->input->get('d');
        if (!empty($discount_code)) {
            $decoded = base64_decode($discount_code);
            if ($decoded !== false && is_numeric($decoded)) {
                $discount_percent = floatval($decoded);
                // Validate discount is within range
                $settings = $this->Settings_model->get_all();
                $discount_min = isset($settings['discount_min']) ? floatval($settings['discount_min']) : 0;
                $discount_max = isset($settings['discount_max']) ? floatval($settings['discount_max']) : 50;
                if ($discount_percent < $discount_min || $discount_percent > $discount_max) {
                    $discount_percent = null;
                }
            }
        }
        
        // Handle lead submission
        if ($this->input->post('submit_lead')) {
            $lead_data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'location' => $this->input->post('location'),
                'prefer_date' => $this->input->post('prefer_date'),
                'detail' => $this->input->post('detail'),
                'affiliate_id' => $affiliate->id,
                'status' => 'pending',
                'discount_percent' => $discount_percent // Save discount if present
            ];
            
            $lead_id = $this->Lead_model->create($lead_data);
            
            if ($lead_id) {
                $this->session->set_flashdata('lead_success', 'Thank you! Your information has been submitted successfully.');
                redirect(current_url());
            }
        }
        
        // Get affiliate stats
        $stats = $this->Affiliate_model->get_stats($affiliate->id);
        
        // Default cover: affiliate's cover OR settings default (jab admin ne affiliate pe set nahi kiya)
        $settings = $this->Settings_model->get_all();
        $default_cover = isset($settings['default_cover_image']) && !empty($settings['default_cover_image']) ? $settings['default_cover_image'] : '';
        
        $data = [
            'affiliate' => $affiliate,
            'stats' => $stats,
            'discount_percent' => $discount_percent,
            'default_cover_image' => $default_cover
        ];
        
        $this->load->view('affiliate/landing', $data);
    }

    // Dashboard
    public function dashboard() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        $affiliate = $this->Affiliate_model->get_by_id($affiliate_id);

        // Get stats
        $stats = $this->Affiliate_model->get_stats($affiliate_id);

        // Get recent commissions
        $commissions = $this->Commission_model->get_by_affiliate($affiliate_id, [], 10);

        // Get weekly performance data for graph
        $graph_data = $this->Affiliate_model->get_weekly_performance($affiliate_id);
        
        // Credit amount from settings - commission earned = remaining (sab ke liye same)
        $commission_earned = (float) $stats['total_commission'];
        $settings = $this->Settings_model->get_all();
        $credit_base = (isset($settings['credit_amount']) && $settings['credit_amount'] !== '') 
            ? (float) $settings['credit_amount'] 
            : 100000; // default 1 lakh
        $credit_balance = max(0, $credit_base - $commission_earned);

        // Can withdraw when full credit earned (commission >= credit_base)
        $can_withdraw = ($credit_base > 0 && $commission_earned >= $credit_base);
        $has_pending_withdraw = ($this->db->table_exists('withdraw_requests')) ? $this->Withdraw_request_model->has_pending($affiliate_id) : false;

        $data = [
            'affiliate' => $affiliate,
            'stats' => $stats,
            'commissions' => $commissions,
            'graph_data' => $graph_data,
            'credit_balance' => $credit_balance,
            'credit_base' => $credit_base,
            'commission_earned' => $commission_earned,
            'can_withdraw' => $can_withdraw && !$has_pending_withdraw,
            'has_pending_withdraw' => $has_pending_withdraw
        ];

        $this->load->view('affiliate/dashboard', $data);
    }

    // Profile
    public function profile() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        $affiliate = $this->Affiliate_model->get_by_id($affiliate_id);
        
        if ($this->input->post()) {
            $update_data = [
                'full_name' => $this->input->post('full_name'),
                'website' => $this->input->post('website'),
                'bio' => $this->input->post('bio'),
                'hubspot_token' => $this->input->post('hubspot_token')
            ];
            $upload_error = false;
            
            // Handle profile picture upload
            if (!empty($_FILES['profile_picture']['name'])) {
                // Create directory if not exists (0777 for web server write permission)
                $upload_path = FCPATH . 'uploads/profile/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                    chmod($upload_path, 0777);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|bmp|ico|tiff|tif';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('profile_picture')) {
                    $upload_data = $this->upload->data();
                    $update_data['profile_picture'] = 'uploads/profile/' . $upload_data['file_name'];
                } else {
                    $this->session->set_flashdata('error', 'Profile picture upload failed: ' . $this->upload->display_errors());
                    $upload_error = true;
                }
            }
            
            // Handle cover/banner image upload (only if affiliate is special)
            if (!empty($_FILES['cover_image']['name'])) {
                // Check if affiliate is special (allowed to change banner)
                if ($affiliate->is_special == 1) {
                    // Create directory if not exists (0777 for web server write permission)
                    $upload_path = FCPATH . 'uploads/cover/';
                    if (!is_dir($upload_path)) {
                        mkdir($upload_path, 0777, true);
                        chmod($upload_path, 0777);
                    }
                    
                    $config['upload_path'] = $upload_path;
                    $config['allowed_types'] = 'gif|jpg|jpeg|png|webp|bmp|ico|tiff|tif';
                    $config['max_size'] = 3072; // 3MB for banner
                    $config['encrypt_name'] = TRUE;
                    
                    $this->load->library('upload', $config);
                    
                    if ($this->upload->do_upload('cover_image')) {
                        $upload_data = $this->upload->data();
                        $update_data['cover_image'] = 'uploads/cover/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('error', 'Banner image upload failed: ' . $this->upload->display_errors());
                        $upload_error = true;
                    }
                } else {
                    $this->session->set_flashdata('error', 'You are not allowed to change banner image. Contact admin.');
                }
            }
            
            if ($this->Affiliate_model->update($affiliate_id, $update_data)) {
                if (!$upload_error) {
                    $this->session->set_flashdata('success', 'Profile updated successfully!');
                }
            } else {
                $this->session->set_flashdata('error', 'Failed to update profile');
            }
            
            redirect('affiliate/profile');
        }
        
        $data['affiliate'] = $affiliate;
        $this->load->view('affiliate/profile', $data);
    }

    // Change Password
    public function change_password() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        
        if ($this->input->post()) {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
            $confirm_password = $this->input->post('confirm_password');
            
            $affiliate = $this->Affiliate_model->get_by_id($affiliate_id);
            
            if (md5($current_password) !== $affiliate->password) {
                $this->session->set_flashdata('error', 'Current password is incorrect');
            } elseif ($new_password !== $confirm_password) {
                $this->session->set_flashdata('error', 'New passwords do not match');
            } elseif (strlen($new_password) < 6) {
                $this->session->set_flashdata('error', 'Password must be at least 6 characters');
            } else {
                $this->Affiliate_model->update($affiliate_id, ['password' => $new_password]);
                $this->session->set_flashdata('success', 'Password changed successfully!');
            }
            
            redirect('affiliate/change_password');
        }
        
        $this->load->view('affiliate/change_password');
    }

    // Withdraw request - when full credit earned
    public function withdraw_request() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        if (!$affiliate_id || !$this->input->post()) {
            redirect('affiliate/dashboard');
            return;
        }
        $settings = $this->Settings_model->get_all();
        $credit_base = isset($settings['credit_amount']) ? (float)$settings['credit_amount'] : 0;
        $stats = $this->Affiliate_model->get_stats($affiliate_id);
        $commission_earned = (float) $stats['total_commission'];
        if ($credit_base <= 0 || $commission_earned < $credit_base) {
            $this->session->set_flashdata('error', 'Withdraw karne ke liye pehle pura credit earn karein');
            redirect('affiliate/dashboard');
            return;
        }
        if ($this->Withdraw_request_model->has_pending($affiliate_id)) {
            $this->session->set_flashdata('error', 'Aapki pehli request abhi pending hai');
            redirect('affiliate/dashboard');
            return;
        }
        if (!$this->db->table_exists('withdraw_requests')) {
            $this->session->set_flashdata('error', 'Withdraw system ready nahi. Pehle run_withdraw_migration.php run karein.');
            redirect('affiliate/dashboard');
            return;
        }
        $bank_name = trim($this->input->post('bank_name'));
        $account_number = trim($this->input->post('account_number'));
        $create_data = [
            'affiliate_id' => $affiliate_id,
            'amount' => $commission_earned,
            'status' => 'pending',
            'bank_name' => $bank_name ?: null,
            'account_number' => $account_number ?: null
        ];
        $id = $this->Withdraw_request_model->create($create_data);
        if ($id) {
            $this->session->set_flashdata('success', 'Request submitted successfully!');
        } else {
            $this->session->set_flashdata('error', 'Request fail ho gayi. Dobara try karein.');
        }
        redirect('affiliate/dashboard');
    }

    // Commissions
    public function commissions() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        $commissions = $this->Commission_model->get_by_affiliate($affiliate_id);
        
        $data['commissions'] = $commissions;
        $this->load->view('affiliate/commissions', $data);
    }

    // Generate Affiliate Links
    public function links() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        $affiliate = $this->Affiliate_model->get_by_id($affiliate_id);
        
        // Get discount settings - check per-affiliate first, then global
        $discount_min = null;
        $discount_max = null;
        
        // Check if affiliate has individual limits set
        if (isset($affiliate->discount_min) && $affiliate->discount_min !== null) {
            $discount_min = floatval($affiliate->discount_min);
        }
        if (isset($affiliate->discount_max) && $affiliate->discount_max !== null) {
            $discount_max = floatval($affiliate->discount_max);
        }
        
        // If not set individually, use global settings
        if ($discount_min === null || $discount_max === null) {
            $settings = $this->Settings_model->get_all();
            if ($discount_min === null) {
                $discount_min = isset($settings['discount_min']) ? floatval($settings['discount_min']) : 0;
            }
            if ($discount_max === null) {
                $discount_max = isset($settings['discount_max']) ? floatval($settings['discount_max']) : 50;
            }
        }
        
        $profile_link = base_url($affiliate->slug);
        $signup_link = base_url('auth/signup?aff=' . $affiliate_id);
        
        $data = [
            'affiliate' => $affiliate,
            'profile_link' => $profile_link,
            'signup_link' => $signup_link,
            'discount_min' => $discount_min,
            'discount_max' => $discount_max
        ];
        
        $this->load->view('affiliate/links', $data);
    }

    // Generate Discount Link
    public function generate_discount_link() {
        $affiliate_id = $this->session->userdata('affiliate_id');
        $affiliate = $this->Affiliate_model->get_by_id($affiliate_id);
        
        // Get discount settings - check per-affiliate first, then global
        $discount_min = null;
        $discount_max = null;
        
        // Check if affiliate has individual limits set
        if (isset($affiliate->discount_min) && $affiliate->discount_min !== null) {
            $discount_min = floatval($affiliate->discount_min);
        }
        if (isset($affiliate->discount_max) && $affiliate->discount_max !== null) {
            $discount_max = floatval($affiliate->discount_max);
        }
        
        // If not set individually, use global settings
        if ($discount_min === null || $discount_max === null) {
            $settings = $this->Settings_model->get_all();
            if ($discount_min === null) {
                $discount_min = isset($settings['discount_min']) ? floatval($settings['discount_min']) : 0;
            }
            if ($discount_max === null) {
                $discount_max = isset($settings['discount_max']) ? floatval($settings['discount_max']) : 50;
            }
        }
        
        $discount_percent = $this->input->post('discount_percent');
        
        // Validate discount
        if (empty($discount_percent) || !is_numeric($discount_percent)) {
            $this->session->set_flashdata('error', 'Please enter a valid discount percentage');
            redirect('affiliate/links');
            return;
        }
        
        $discount_percent = floatval($discount_percent);
        
        if ($discount_percent < $discount_min || $discount_percent > $discount_max) {
            $this->session->set_flashdata('error', 'Discount must be between ' . $discount_min . '% and ' . $discount_max . '%');
            redirect('affiliate/links');
            return;
        }
        
        // Encode discount in base64
        $discount_encoded = base64_encode((string)$discount_percent);
        
        // Generate link with discount parameter
        $discount_link = base_url($affiliate->slug) . '?d=' . urlencode($discount_encoded);
        
        $profile_link = base_url($affiliate->slug);
        $signup_link = base_url('auth/signup?aff=' . $affiliate_id);
        
        $data = [
            'affiliate' => $affiliate,
            'profile_link' => $profile_link,
            'signup_link' => $signup_link,
            'discount_min' => $discount_min,
            'discount_max' => $discount_max,
            'generated_discount_link' => $discount_link,
            'discount_percent' => $discount_percent
        ];
        
        $this->load->view('affiliate/links', $data);
    }
}
