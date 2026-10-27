<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Click_model', 'Settings_model']);
        
        // Track affiliate click if affiliate ID in URL
        $affiliate_id = $this->input->get('aff');
        if ($affiliate_id) {
            setcookie('affiliate_id', $affiliate_id, time() + (30 * 24 * 60 * 60), '/');
            $this->Click_model->record($affiliate_id);
        }
    }

    public function index() {
        // Load all settings
        $settings = $this->Settings_model->get_all();
        
        $data = [
            'settings' => $settings
        ];
        
        $this->load->view('home/new_landing', $data);
    }
}

