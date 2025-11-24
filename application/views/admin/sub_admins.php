<?php $this->load->view('layouts/header', ['title' => 'Sub-Admins']); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-shield-alt"></i> Admin</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/dashboard'); ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/affiliates'); ?>">
                        <i class="fas fa-users"></i> Affiliates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/leads'); ?>">
                        <i class="fas fa-list"></i> Leads
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/commissions'); ?>">
                        <i class="fas fa-dollar-sign"></i> Commissions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/settings'); ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <?php if (isset($is_super_admin) && $is_super_admin): ?>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('admin/sub_admins'); ?>">
                        <i class="fas fa-user-shield"></i> Sub-Admins
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/activity_logs'); ?>">
                        <i class="fas fa-history"></i> Activity Logs
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/change_password'); ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/logout'); ?>">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-shield"></i> Sub-Admins Management</h5>
                    <a href="<?php echo base_url('admin/add_sub_admin'); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Sub-Admin
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
                    <?php endif; ?>

                    <!-- Filters -->
                    <form method="GET" class="mb-4 row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, username, email..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo ($filters['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="super_admin" <?php echo ($filters['role'] == 'super_admin') ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo base_url('admin/sub_admins'); ?>" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>

                    <!-- Admins Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($admins)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No admins found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td><?php echo $admin->id; ?></td>
                                            <td><?php echo htmlspecialchars($admin->full_name); ?></td>
                                            <td><?php echo htmlspecialchars($admin->username); ?></td>
                                            <td><?php echo htmlspecialchars($admin->email); ?></td>
                                            <td>
                                                <span class="badge <?php echo $admin->role == 'super_admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $admin->role)); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $admin->last_login ? date('Y-m-d H:i', strtotime($admin->last_login)) : 'Never'; ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($admin->created_at)); ?></td>
                                            <td>
                                                <?php if ($admin->role != 'super_admin' || $admin->id == $this->session->userdata('admin_id')): ?>
                                                    <a href="<?php echo base_url('admin/edit_sub_admin/' . $admin->id); ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <?php if ($admin->id != $this->session->userdata('admin_id')): ?>
                                                        <a href="<?php echo base_url('admin/delete_sub_admin/' . $admin->id); ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this sub-admin?');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

