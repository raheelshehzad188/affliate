<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Affiliate_model');
        $this->load->library('session');
    }

    // Signup Form
    public function signup() {
        if ($this->session->userdata('affiliate_id')) {
            redirect('affiliate/dashboard');
        }

        $this->load->library('form_validation');
        
        if ($this->input->post()) {
            $this->form_validation->set_rules('username', 'Username', 'required|callback_check_username');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_check_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('full_name', 'Full Name', 'required');

            if ($this->form_validation->run()) {
                $data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'password' => $this->input->post('password'),
                    'full_name' => $this->input->post('full_name'),
                    'website' => $this->input->post('website'),
                    'promote_method' => $this->input->post('promote_method'),
                    'status' => 'pending'
                ];

                // Check if referred by (from URL parameter or cookie)
                $ref_id = $this->input->get('aff'); // URL parameter: ?aff=123
                if (!$ref_id) {
                    $ref_id = $this->input->cookie('affiliate_id'); // Cookie
                }
                if ($ref_id) {
                    // Verify that referral affiliate exists and is active
                    $referrer = $this->Affiliate_model->get_by_id($ref_id);
                    if ($referrer && $referrer->status === 'active') {
                        $data['referred_by'] = $ref_id;
                        // Set cookie for future tracking
                        setcookie('affiliate_id', $ref_id, time() + (30 * 24 * 60 * 60), '/');
                    }
                }

                $affiliate_id = $this->Affiliate_model->create($data);
                
                if ($affiliate_id) {
                    // Send verification email
                    $affiliate = $this->Affiliate_model->get_by_id($affiliate_id);
                    $this->send_verification_email($affiliate);
                    
                    $this->session->set_flashdata('success', 'Account created! Please check your email to verify.');
                    redirect('auth/signup');
                }
            }
        }

        $this->load->view('auth/signup');
    }

    // Login
    public function login() {
        if ($this->session->userdata('affiliate_id')) {
            redirect('affiliate/dashboard');
        }

        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $affiliate = $this->Affiliate_model->verify_login($username, $password);
            
            if ($affiliate) {
                $this->session->set_userdata([
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->full_name,
                    'affiliate_username' => $affiliate->username
                ]);
                
                // Set cookie for tracking
                setcookie('affiliate_user_id', $affiliate->id, time() + (30 * 24 * 60 * 60), '/');
                
                redirect('affiliate/dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid credentials or account not active');
            }
        }

        $this->load->view('auth/login');
    }

    // Logout
    public function logout() {
        $this->session->unset_userdata(['affiliate_id', 'affiliate_name', 'affiliate_username']);
        setcookie('affiliate_user_id', '', time() - 3600, '/');
        redirect('auth/login');
    }

    // Verify Email
    public function verify_email($token = '') {
        if ($this->Affiliate_model->verify_email_token($token)) {
            $this->session->set_flashdata('success', 'Email verified successfully! You can now login.');
        } else {
            $this->session->set_flashdata('error', 'Invalid or expired verification link.');
        }
        redirect('auth/login');
    }

    // Send verification email
    private function send_verification_email($affiliate) {
        $verification_link = base_url('auth/verify_email/' . $affiliate->verification_token);
        
        $message = "
        <html>
        <body>
            <h2>Welcome {$affiliate->full_name}!</h2>
            <p>Thank you for signing up. Please verify your email by clicking the link below:</p>
            <p><a href='{$verification_link}'>Verify Email</a></p>
        </body>
        </html>
        ";

        $this->load->library('email');
        $this->email->from('noreply@example.com', 'Affiliate System');
        $this->email->to($affiliate->email);
        $this->email->subject('Verify Your Email');
        $this->email->message($message);
        $this->email->send();
    }
    
    // Custom validation for username
    public function check_username($username) {
        $existing = $this->Affiliate_model->get_by_username($username);
        if ($existing) {
            $this->form_validation->set_message('check_username', 'Username already exists');
            return FALSE;
        }
        return TRUE;
    }
    
    // Custom validation for email
    public function check_email($email) {
        $existing = $this->Affiliate_model->get_by_email($email);
        if ($existing) {
            $this->form_validation->set_message('check_email', 'Email already exists');
            return FALSE;
        }
        return TRUE;
    }
}

