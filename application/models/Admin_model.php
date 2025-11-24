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
            // Update last login
            $this->db->where('id', $admin->id)->update('admin_users', ['last_login' => date('Y-m-d H:i:s')]);
            return $admin;
        }
        return false;
    }

    // Get admin by ID
    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('admin_users')->row();
    }

    // Get admin by username
    public function get_by_username($username) {
        return $this->db->where('username', $username)->get('admin_users')->row();
    }

    // Get admin by email
    public function get_by_email($email) {
        return $this->db->where('email', $email)->get('admin_users')->row();
    }

    // Get all admins
    public function get_all($filters = []) {
        if (isset($filters['role']) && !empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start();
            $this->db->like('username', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('full_name', $search);
            $this->db->group_end();
        }
        
        return $this->db->order_by('created_at', 'DESC')->get('admin_users')->result();
    }

    // Create admin
    public function create($data) {
        $admin_data = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => md5($data['password']),
            'full_name' => isset($data['full_name']) ? $data['full_name'] : null,
            'role' => isset($data['role']) ? $data['role'] : 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('admin_users', $admin_data) ? $this->db->insert_id() : false;
    }

    // Update admin
    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }
        return $this->db->where('id', $id)->update('admin_users', $data);
    }

    // Delete admin
    public function delete($id) {
        // Don't allow deleting super_admin
        $admin = $this->get_by_id($id);
        if ($admin && $admin->role === 'super_admin') {
            return false;
        }
        return $this->db->where('id', $id)->delete('admin_users');
    }

    // Check if user is super admin
    public function is_super_admin($admin_id) {
        $admin = $this->get_by_id($admin_id);
        return $admin && $admin->role === 'super_admin';
    }
}

