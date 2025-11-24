<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Admin_model', 'Affiliate_model', 'Lead_model', 'Commission_model', 'Click_model', 'Settings_model', 'Activity_log_model']);
        
        // Don't check authentication for login and logout methods
        $current_method = $this->uri->segment(2);
        $allowed_methods = array('login', 'logout');
        
        if (!in_array($current_method, $allowed_methods)) {
            if (!$this->session->userdata('admin_id')) {
                redirect('admin/login');
            }
        }
    }

    // Helper method to check if current user is super admin
    private function check_is_super_admin() {
        $admin_id = $this->session->userdata('admin_id');
        return $admin_id ? $this->Admin_model->is_super_admin($admin_id) : false;
    }

    // Helper method to log activity
    private function log_activity($action_type, $action_description, $entity_type = null, $entity_id = null, $old_data = null, $new_data = null) {
        $admin_id = $this->session->userdata('admin_id');
        $admin_name = $this->session->userdata('admin_name');
        
        $this->Activity_log_model->create([
            'admin_id' => $admin_id,
            'admin_name' => $admin_name,
            'action_type' => $action_type,
            'action_description' => $action_description,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'old_data' => $old_data,
            'new_data' => $new_data
        ]);
    }

    // Dashboard
    public function dashboard() {
        $from_date = $this->input->get('from') ?: date('Y-m-01');
        $to_date = $this->input->get('to') ?: date('Y-m-t');
        
        $data = [
            'total_affiliates' => count($this->Affiliate_model->get_all(['status' => 'active'])),
            'pending_affiliates' => count($this->Affiliate_model->get_all(['status' => 'pending'])),
            'total_clicks' => $this->Click_model->count(null, $from_date, $to_date),
            'total_leads' => $this->Lead_model->count([], $from_date, $to_date),
            'confirmed_leads' => $this->Lead_model->count(['status' => 'confirmed'], $from_date, $to_date),
            'from_date' => $from_date,
            'to_date' => $to_date,
            'is_super_admin' => $this->check_is_super_admin()
        ];
        
        $this->load->view('admin/dashboard', $data);
    }

    // Affiliates List
    public function affiliates() {
        // Get filters and clean empty values
        $filters = [];
        
        $status = $this->input->get('status');
        if (!empty($status)) {
            $filters['status'] = $status;
        }
        
        $search = $this->input->get('search');
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        
        $from_date = $this->input->get('from_date');
        if (!empty($from_date)) {
            $filters['from_date'] = $from_date;
        }
        
        $to_date = $this->input->get('to_date');
        if (!empty($to_date)) {
            $filters['to_date'] = $to_date;
        }
        
        $affiliates = $this->Affiliate_model->get_all($filters);
        
        // Prepare filters for view (with all keys for form)
        $view_filters = [
            'status' => $status ?: '',
            'search' => $search ?: '',
            'from_date' => $from_date ?: date('Y-m-01'),
            'to_date' => $to_date ?: date('Y-m-t')
        ];
        
        $data = [
            'affiliates' => $affiliates,
            'filters' => $view_filters
        ];
        
        $this->load->view('admin/affiliates', $data);
    }

    // Approve/Reject Affiliate
    public function approve_affiliate($id) {
        $status = $this->input->post('status');
        if ($status && in_array($status, ['active', 'inactive', 'pending'])) {
            $affiliate = $this->Affiliate_model->get_by_id($id);
            $old_status = $affiliate ? $affiliate->status : null;
            
            if ($this->Affiliate_model->update($id, ['status' => $status])) {
                $this->log_activity(
                    'affiliate_status_update',
                    "Affiliate status changed from '{$old_status}' to '{$status}'",
                    'affiliate',
                    $id,
                    ['status' => $old_status],
                    ['status' => $status]
                );
                $this->session->set_flashdata('success', 'Affiliate status updated');
            }
        }
        redirect('admin/affiliates');
    }

    // View Affiliate Detail
    public function affiliate_detail($id) {
        $affiliate = $this->Affiliate_model->get_by_id($id);
        
        if ($this->input->post()) {
            $update_data = [
                'full_name' => $this->input->post('full_name'),
                'status' => $this->input->post('status'),
                'website' => $this->input->post('website'),
                'is_special' => $this->input->post('is_special') ? 1 : 0
            ];
            
            // Handle discount limits (can be empty to use global settings)
            $discount_min = $this->input->post('discount_min');
            $discount_max = $this->input->post('discount_max');
            
            // Only update if fields are present in form (even if empty)
            if ($discount_min !== false) {
                if ($discount_min !== '' && $discount_min !== null) {
                    $update_data['discount_min'] = floatval($discount_min);
                } else {
                    $update_data['discount_min'] = null; // Use global setting
                }
            }
            
            if ($discount_max !== false) {
                if ($discount_max !== '' && $discount_max !== null) {
                    $update_data['discount_max'] = floatval($discount_max);
                } else {
                    $update_data['discount_max'] = null; // Use global setting
                }
            }
            
            // Update slug if provided
            $new_slug = trim($this->input->post('slug'));
            if (!empty($new_slug)) {
                // Clean slug (lowercase, replace spaces with dashes, remove special chars)
                $new_slug = strtolower($new_slug);
                $new_slug = preg_replace('/[^a-z0-9-]+/', '-', $new_slug);
                $new_slug = preg_replace('/-+/', '-', $new_slug);
                $new_slug = trim($new_slug, '-');
                
                if (strlen($new_slug) >= 3) {
                    // Check if slug is unique (excluding current affiliate)
                    $existing = $this->Affiliate_model->get_by_slug($new_slug);
                    if (!$existing || $existing->id == $id) {
                        $update_data['slug'] = $new_slug;
                    } else {
                        $this->session->set_flashdata('error', 'Slug already exists. Please choose a different one.');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Slug must be at least 3 characters long.');
                }
            }
            
            // Handle profile picture upload
            if (!empty($_FILES['profile_picture']['name'])) {
                $upload_path = FCPATH . 'uploads/profile/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('profile_picture')) {
                    $upload_data = $this->upload->data();
                    $update_data['profile_picture'] = 'uploads/profile/' . $upload_data['file_name'];
                } else {
                    $this->session->set_flashdata('error', 'Profile picture upload failed: ' . $this->upload->display_errors());
                }
            }
            
            // Handle cover/banner image upload
            if (!empty($_FILES['cover_image']['name'])) {
                $upload_path = FCPATH . 'uploads/cover/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                
                $config['upload_path'] = $upload_path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size'] = 3072; // 3MB for banner
                $config['encrypt_name'] = TRUE;
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('cover_image')) {
                    $upload_data = $this->upload->data();
                    $update_data['cover_image'] = 'uploads/cover/' . $upload_data['file_name'];
                } else {
                    $this->session->set_flashdata('error', 'Banner image upload failed: ' . $this->upload->display_errors());
                }
            }
            
            // Update password if provided
            $new_password = $this->input->post('password');
            if (!empty($new_password)) {
                if (strlen($new_password) >= 6) {
                    // Pass plain password - model will hash it
                    $update_data['password'] = $new_password;
                } else {
                    $this->session->set_flashdata('error', 'Password must be at least 6 characters long.');
                    redirect('admin/affiliate_detail/' . $id);
                    return;
                }
            }
            
            // Remove empty string values but keep null values (for discount limits)
            $update_data = array_filter($update_data, function($value, $key) {
                // Keep null values (for discount limits to reset to global)
                if ($value === null) {
                    return true;
                }
                // Remove empty strings
                return $value !== '';
            }, ARRAY_FILTER_USE_BOTH);
            
            // Always include is_special (default 0 if not set)
            if (!isset($update_data['is_special'])) {
                $update_data['is_special'] = 0;
            }
            
            if (!empty($update_data)) {
                // Get old data for logging
                $old_affiliate = $this->Affiliate_model->get_by_id($id);
                $old_data = [
                    'full_name' => $old_affiliate->full_name,
                    'status' => $old_affiliate->status,
                    'website' => $old_affiliate->website,
                    'is_special' => $old_affiliate->is_special,
                    'discount_min' => $old_affiliate->discount_min,
                    'discount_max' => $old_affiliate->discount_max,
                    'slug' => $old_affiliate->slug
                ];
                
                if ($this->Affiliate_model->update($id, $update_data)) {
                    // Log the update
                    $this->log_activity(
                        'affiliate_update',
                        "Affiliate '{$old_affiliate->full_name}' (ID: {$id}) updated",
                        'affiliate',
                        $id,
                        $old_data,
                        $update_data
                    );
                    $this->session->set_flashdata('success', 'Affiliate updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update affiliate. Please check database connection and column exists.');
                }
            } else {
                $this->session->set_flashdata('error', 'No data to update');
            }
            
            redirect('admin/affiliate_detail/' . $id);
        }
        
        $stats = $this->Affiliate_model->get_stats($id);
        
        $data = [
            'affiliate' => $affiliate,
            'stats' => $stats
        ];
        
        $this->load->view('admin/affiliate_detail', $data);
    }

    // Leads List
    public function leads() {
        // Get filters and clean empty values
        $filters = [];
        
        $affiliate_id = $this->input->get('affiliate_id');
        if (!empty($affiliate_id)) {
            $filters['affiliate_id'] = $affiliate_id;
        }
        
        $status = $this->input->get('status');
        if (!empty($status)) {
            $filters['status'] = $status;
        }
        
        $from_date = $this->input->get('from_date');
        if (!empty($from_date)) {
            $filters['from_date'] = $from_date;
        }
        
        $to_date = $this->input->get('to_date');
        if (!empty($to_date)) {
            $filters['to_date'] = $to_date;
        }
        
        $page = $this->input->get('page') ?: 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        $leads = $this->Lead_model->get_all($filters, $per_page, $offset);
        $total = $this->Lead_model->count($filters);
        
        // Prepare filters for view (with all keys for form)
        $view_filters = [
            'affiliate_id' => $affiliate_id ?: '',
            'status' => $status ?: '',
            'from_date' => $from_date ?: '',
            'to_date' => $to_date ?: ''
        ];
        
        $data = [
            'leads' => $leads,
            'filters' => $view_filters,
            'pagination' => [
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page
            ],
            'affiliates' => $this->Affiliate_model->get_all()
        ];
        
        $this->load->view('admin/leads', $data);
    }

    // Confirm Lead
    public function confirm_lead($id) {
        if ($this->input->post()) {
            $sale_amount = $this->input->post('sale_amount');
            $feedback = $this->input->post('feedback');
            
            // Get old lead data for logging
            $old_lead = $this->Lead_model->get_by_id($id);
            
            // First confirm the lead
            if ($this->Lead_model->confirm($id, $sale_amount, $feedback)) {
                // Process commissions (will be created with 'confirmed' status)
                $this->Commission_model->process_commissions_by_lead($id, $sale_amount);
                
                // Also update any existing commissions for this lead to 'confirmed'
                $this->Commission_model->update_status_by_lead($id, 'pending');
                
                // Log the lead confirmation
                $this->log_activity(
                    'lead_confirmed',
                    "Lead #{$id} ({$old_lead->name}) confirmed with sale amount: $" . number_format($sale_amount, 2),
                    'lead',
                    $id,
                    ['status' => $old_lead->status, 'sale_amount' => $old_lead->sale_amount],
                    ['status' => 'confirmed', 'sale_amount' => $sale_amount, 'feedback' => $feedback]
                );
                
                $this->session->set_flashdata('success', 'Lead confirmed and commissions processed');
            } else {
                $this->session->set_flashdata('error', 'Failed to confirm lead');
            }
        }
        redirect('admin/leads');
    }

    // Commissions List
    public function commissions() {
        $filters = [
            'affiliate_id' => $this->input->get('affiliate_id'),
            'status' => $this->input->get('status'),
            'from_date' => $this->input->get('from_date'),
            'to_date' => $this->input->get('to_date')
        ];
        
        $commissions = $this->Commission_model->get_all($filters);
        
        $data = [
            'commissions' => $commissions,
            'filters' => $filters,
            'affiliates' => $this->Affiliate_model->get_all()
        ];
        
        $this->load->view('admin/commissions', $data);
    }

    // Admin Login
    public function login() {
        if ($this->session->userdata('admin_id')) {
            redirect('admin/dashboard');
        }
        
        if ($this->input->post()) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            
            $admin = $this->Admin_model->verify_login($username, $password);
            
            if ($admin) {
                $this->session->set_userdata([
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->full_name,
                    'admin_username' => $admin->username,
                    'admin_role' => $admin->role
                ]);
                redirect('admin/dashboard');
            } else {
                $this->session->set_flashdata('error', 'Invalid credentials');
            }
        }
        
        $this->load->view('admin/login');
    }

    // Admin Logout
    public function logout() {
        $this->session->unset_userdata(['admin_id', 'admin_name', 'admin_username', 'admin_role']);
        redirect('admin/login');
    }
    
    // Change Password
    public function change_password() {
        $admin_id = $this->session->userdata('admin_id');
        
        if ($this->input->post()) {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
            $confirm_password = $this->input->post('confirm_password');
            
            $admin = $this->Admin_model->get_by_id($admin_id);
            
            if (md5($current_password) !== $admin->password) {
                $this->session->set_flashdata('error', 'Current password is incorrect');
            } elseif ($new_password !== $confirm_password) {
                $this->session->set_flashdata('error', 'New passwords do not match');
            } elseif (strlen($new_password) < 6) {
                $this->session->set_flashdata('error', 'Password must be at least 6 characters');
            } else {
                $this->Admin_model->update($admin_id, ['password' => md5($new_password)]);
                $this->session->set_flashdata('success', 'Password changed successfully!');
            }
            
            redirect('admin/change_password');
        }
        
        $this->load->view('admin/change_password');
    }

    // Settings
    public function settings() {
        $tab = $this->input->get('tab') ?: 'general';
        
        // Check if settings table exists
        $table_exists = $this->db->table_exists('settings');
        
        if ($this->input->post()) {
            if (!$table_exists) {
                $this->session->set_flashdata('error', 'Settings table does not exist. Please run the create_settings_table.php script first.');
                redirect('admin/settings?tab=' . $tab);
                return;
            }
            
            $settings_data = $this->input->post();
            
            // Remove submit button from data
            unset($settings_data['submit']);
            
            // Update settings
            if ($this->Settings_model->update_batch($settings_data)) {
                $this->session->set_flashdata('success', 'Settings updated successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to update settings');
            }
            
            redirect('admin/settings?tab=' . $tab);
        }
        
        // Get all settings
        $settings = $this->Settings_model->get_all();
        
        $data = [
            'settings' => $settings,
            'active_tab' => $tab,
            'table_exists' => $table_exists
        ];
        
        $this->load->view('admin/settings', $data);
    }

    // Sub-Admin Management (Only for super_admin)
    public function sub_admins() {
        // Check if user is super admin
        $admin_id = $this->session->userdata('admin_id');
        if (!$this->Admin_model->is_super_admin($admin_id)) {
            $this->session->set_flashdata('error', 'Access denied. Only super admin can manage sub-admins.');
            redirect('admin/dashboard');
            return;
        }

        $filters = [];
        $search = $this->input->get('search');
        if (!empty($search)) {
            $filters['search'] = $search;
        }

        $role = $this->input->get('role');
        if (!empty($role)) {
            $filters['role'] = $role;
        }

        $admins = $this->Admin_model->get_all($filters);
        
        $data = [
            'admins' => $admins,
            'filters' => [
                'search' => $search ?: '',
                'role' => $role ?: ''
            ],
            'is_super_admin' => true
        ];
        
        $this->load->view('admin/sub_admins', $data);
    }

    // Add Sub-Admin
    public function add_sub_admin() {
        // Check if user is super admin
        $admin_id = $this->session->userdata('admin_id');
        if (!$this->Admin_model->is_super_admin($admin_id)) {
            $this->session->set_flashdata('error', 'Access denied. Only super admin can add sub-admins.');
            redirect('admin/dashboard');
            return;
        }

        if ($this->input->post()) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('username', 'Username', 'required|callback_check_admin_username');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_check_admin_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('full_name', 'Full Name', 'required');

            if ($this->form_validation->run()) {
                $data = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'password' => $this->input->post('password'),
                    'full_name' => $this->input->post('full_name'),
                    'role' => 'admin' // Sub-admins are always 'admin', not 'super_admin'
                ];

                $new_admin_id = $this->Admin_model->create($data);
                
                if ($new_admin_id) {
                    // Log the creation
                    $this->log_activity(
                        'sub_admin_created',
                        "Sub-admin '{$data['full_name']}' (Username: {$data['username']}) created",
                        'admin_user',
                        $new_admin_id,
                        null,
                        ['username' => $data['username'], 'email' => $data['email'], 'full_name' => $data['full_name']]
                    );
                    $this->session->set_flashdata('success', 'Sub-admin created successfully');
                    redirect('admin/sub_admins');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create sub-admin');
                }
            }
        }

        $data = ['is_super_admin' => true];
        $this->load->view('admin/add_sub_admin', $data);
    }

    // Edit Sub-Admin
    public function edit_sub_admin($id) {
        // Check if user is super admin
        $admin_id = $this->session->userdata('admin_id');
        if (!$this->Admin_model->is_super_admin($admin_id)) {
            $this->session->set_flashdata('error', 'Access denied. Only super admin can edit sub-admins.');
            redirect('admin/dashboard');
            return;
        }

        $admin = $this->Admin_model->get_by_id($id);
        if (!$admin) {
            $this->session->set_flashdata('error', 'Admin not found');
            redirect('admin/sub_admins');
            return;
        }

        // Don't allow editing super_admin
        if ($admin->role === 'super_admin' && $admin->id != $admin_id) {
            $this->session->set_flashdata('error', 'Cannot edit another super admin');
            redirect('admin/sub_admins');
            return;
        }

        if ($this->input->post()) {
            $old_data = [
                'username' => $admin->username,
                'email' => $admin->email,
                'full_name' => $admin->full_name,
                'role' => $admin->role
            ];

            $update_data = [
                'full_name' => $this->input->post('full_name'),
                'email' => $this->input->post('email')
            ];

            // Update password if provided
            $new_password = $this->input->post('password');
            if (!empty($new_password)) {
                if (strlen($new_password) >= 6) {
                    $update_data['password'] = $new_password;
                } else {
                    $this->session->set_flashdata('error', 'Password must be at least 6 characters');
                    redirect('admin/edit_sub_admin/' . $id);
                    return;
                }
            }

            if ($this->Admin_model->update($id, $update_data)) {
                // Log the update
                $this->log_activity(
                    'sub_admin_updated',
                    "Sub-admin '{$update_data['full_name']}' (ID: {$id}) updated",
                    'admin_user',
                    $id,
                    $old_data,
                    $update_data
                );
                $this->session->set_flashdata('success', 'Sub-admin updated successfully');
                redirect('admin/sub_admins');
            } else {
                $this->session->set_flashdata('error', 'Failed to update sub-admin');
            }
        }

        $data = [
            'admin' => $admin,
            'is_super_admin' => true
        ];
        $this->load->view('admin/edit_sub_admin', $data);
    }

    // Delete Sub-Admin
    public function delete_sub_admin($id) {
        // Check if user is super admin
        $admin_id = $this->session->userdata('admin_id');
        if (!$this->Admin_model->is_super_admin($admin_id)) {
            $this->session->set_flashdata('error', 'Access denied. Only super admin can delete sub-admins.');
            redirect('admin/dashboard');
            return;
        }

        // Don't allow deleting yourself
        if ($id == $admin_id) {
            $this->session->set_flashdata('error', 'Cannot delete your own account');
            redirect('admin/sub_admins');
            return;
        }

        $admin = $this->Admin_model->get_by_id($id);
        if (!$admin) {
            $this->session->set_flashdata('error', 'Admin not found');
            redirect('admin/sub_admins');
            return;
        }

        if ($this->Admin_model->delete($id)) {
            // Log the deletion
            $this->log_activity(
                'sub_admin_deleted',
                "Sub-admin '{$admin->full_name}' (Username: {$admin->username}) deleted",
                'admin_user',
                $id,
                ['username' => $admin->username, 'email' => $admin->email, 'full_name' => $admin->full_name],
                null
            );
            $this->session->set_flashdata('success', 'Sub-admin deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete sub-admin. Cannot delete super admin.');
        }
        
        redirect('admin/sub_admins');
    }

    // Activity Logs (Only for super_admin)
    public function activity_logs() {
        // Check if user is super admin
        $admin_id = $this->session->userdata('admin_id');
        if (!$this->Admin_model->is_super_admin($admin_id)) {
            $this->session->set_flashdata('error', 'Access denied. Only super admin can view activity logs.');
            redirect('admin/dashboard');
            return;
        }

        // Check if activity_logs table exists
        $table_exists = $this->db->table_exists('activity_logs');
        
        if (!$table_exists) {
            $this->session->set_flashdata('error', 'Activity logs table does not exist. Please run the SQL migration file: all_changes_today.sql');
            redirect('admin/dashboard');
            return;
        }

        $filters = [];
        
        $admin_filter = $this->input->get('admin_id');
        if (!empty($admin_filter)) {
            $filters['admin_id'] = $admin_filter;
        }
        
        $action_type = $this->input->get('action_type');
        if (!empty($action_type)) {
            $filters['action_type'] = $action_type;
        }
        
        $entity_type = $this->input->get('entity_type');
        if (!empty($entity_type)) {
            $filters['entity_type'] = $entity_type;
        }
        
        $from_date = $this->input->get('from_date');
        if (!empty($from_date)) {
            $filters['from_date'] = $from_date;
        }
        
        $to_date = $this->input->get('to_date');
        if (!empty($to_date)) {
            $filters['to_date'] = $to_date;
        }

        $page = $this->input->get('page') ?: 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $logs = $this->Activity_log_model->get_all($filters, $per_page, $offset);
        $total = $this->Activity_log_model->count($filters);

        // Get all admins for filter dropdown
        $all_admins = $this->Admin_model->get_all();

        $data = [
            'logs' => $logs,
            'filters' => [
                'admin_id' => $admin_filter ?: '',
                'action_type' => $action_type ?: '',
                'entity_type' => $entity_type ?: '',
                'from_date' => $from_date ?: '',
                'to_date' => $to_date ?: ''
            ],
            'pagination' => [
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page
            ],
            'admins' => $all_admins,
            'is_super_admin' => true
        ];

        $this->load->view('admin/activity_logs', $data);
    }

    // Validation callbacks for sub-admin
    public function check_admin_username($username) {
        $existing = $this->Admin_model->get_by_username($username);
        if ($existing) {
            $this->form_validation->set_message('check_admin_username', 'Username already exists');
            return FALSE;
        }
        return TRUE;
    }

    public function check_admin_email($email) {
        $existing = $this->Admin_model->get_by_email($email);
        if ($existing) {
            $this->form_validation->set_message('check_admin_email', 'Email already exists');
            return FALSE;
        }
        return TRUE;
    }
}

