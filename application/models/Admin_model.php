<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Verify admin login
    public function verify_login($username, $password) {
        $admin = $this->db->where('username', $username)->get('admin_users')->row();
        if ($admin && md5($password) === $admin->password) {
            return $admin;
        }
        return false;
    }

    // Get admin by ID
    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('admin_users')->row();
    }

    // Update admin
    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password'] = md5($data['password']);
        }
        return $this->db->where('id', $id)->update('admin_users', $data);
    }
}

