<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Affiliate extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Affiliate_model', 'Lead_model', 'Commission_model', 'Click_model']);
        $this->load->library('session');
        
        // Don't check auth for landing page
        $method = $this->uri->segment(2);
        if ($method !== 'landing') {
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
                'status' => 'pending'
            ];
            
            $lead_id = $this->Lead_model->create($lead_data);
            
            if ($lead_id) {
                $this->session->set_flashdata('lead_success', 'Thank you! Your information has been submitted successfully.');
                redirect(current_url());
            }
        }
        
        // Get affiliate stats
        $stats = $this->Affiliate_model->get_stats($affiliate->id);
        
        $data = [
            'affiliate' => $affiliate,
            'stats' => $stats
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
        
        $data = [
            'affiliate' => $affiliate,
            'stats' => $stats,
            'commissions' => $commissions,
            'graph_data' => $graph_data
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
            
            // Handle profile picture upload
            if (!empty($_FILES['profile_picture']['name'])) {
                // Create directory if not exists
                if (!is_dir('./uploads/profile/')) {
                    mkdir('./uploads/profile/', 0777, true);
                }
                
                $config['upload_path'] = './uploads/profile/';
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('profile_picture')) {
                    $upload_data = $this->upload->data();
                    $update_data['profile_picture'] = 'uploads/profile/' . $upload_data['file_name'];
                }
            }
            
            // Handle cover/banner image upload (only if affiliate is special)
            if (!empty($_FILES['cover_image']['name'])) {
                // Check if affiliate is special (allowed to change banner)
                if ($affiliate->is_special == 1) {
                    // Create directory if not exists
                    if (!is_dir('./uploads/cover/')) {
                        mkdir('./uploads/cover/', 0777, true);
                    }
                    
                    $config['upload_path'] = './uploads/cover/';
                    $config['allowed_types'] = 'gif|jpg|jpeg|png';
                    $config['max_size'] = 3072; // 3MB for banner
                    $config['encrypt_name'] = TRUE;
                    
                    $this->load->library('upload', $config);
                    
                    if ($this->upload->do_upload('cover_image')) {
                        $upload_data = $this->upload->data();
                        $update_data['cover_image'] = 'uploads/cover/' . $upload_data['file_name'];
                    }
                } else {
                    $this->session->set_flashdata('error', 'You are not allowed to change banner image. Contact admin.');
                }
            }
            
            if ($this->Affiliate_model->update($affiliate_id, $update_data)) {
                $this->session->set_flashdata('success', 'Profile updated successfully!');
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
        
        $profile_link = base_url($affiliate->slug);
        $signup_link = base_url('auth/signup?aff=' . $affiliate_id);
        
        $data = [
            'affiliate' => $affiliate,
            'profile_link' => $profile_link,
            'signup_link' => $signup_link
        ];
        
        $this->load->view('affiliate/links', $data);
    }
}
