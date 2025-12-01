<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Verify admin login
    public function verify_login($username, $password) {
        // Explicitly select all columns including id
        $admin = $this->db->select('*')
                          ->where('username', $username)
                          ->get('admin_users')
                          ->row();
        
        if ($admin) {
            // Check if admin object has id property
            if (!isset($admin->id) || empty($admin->id)) {
                log_message('error', 'Admin object missing ID field - Username: ' . $username);
                return false;
            }
            
            // Debug: Log what we found
            log_message('debug', 'Admin found - ID: ' . $admin->id . ', Username: ' . $admin->username);
            log_message('debug', 'Password check - Input MD5: ' . md5($password) . ', DB Hash: ' . $admin->password);
            
            if (md5($password) === $admin->password) {
                // Update last login (only if column exists)
                try {
                    $this->db->where('id', $admin->id)->update('admin_users', ['last_login' => date('Y-m-d H:i:s')]);
                } catch (Exception $e) {
                    // Ignore if last_login column doesn't exist
                    log_message('debug', 'Could not update last_login: ' . $e->getMessage());
                }
                
                // Ensure role is set (default to 'admin' if not set)
                if (!isset($admin->role) || empty($admin->role)) {
                    $admin->role = 'admin';
                }
                
                // Ensure full_name is set (default to username if not set)
                if (!isset($admin->full_name) || empty($admin->full_name)) {
                    $admin->full_name = $admin->username;
                }
                
                // Double check ID is still there
                if (!isset($admin->id) || !$admin->id) {
                    log_message('error', 'Admin ID lost after processing - Username: ' . $username);
                    return false;
                }
                
                log_message('debug', 'Login verification successful - ID: ' . $admin->id);
                return $admin;
            } else {
                log_message('debug', 'Password mismatch for username: ' . $username);
            }
        } else {
            log_message('debug', 'Admin not found with username: ' . $username);
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

