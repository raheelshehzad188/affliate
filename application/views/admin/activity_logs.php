<?php $this->load->view('layouts/header', ['title' => 'Activity Logs']); ?>

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
                    <a class="nav-link" href="<?php echo base_url('admin/sub_admins'); ?>">
                        <i class="fas fa-user-shield"></i> Sub-Admins
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('admin/activity_logs'); ?>">
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
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Activity Logs</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4 row g-3">
                        <div class="col-md-3">
                            <select name="admin_id" class="form-select">
                                <option value="">All Admins</option>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?php echo $admin->id; ?>" <?php echo ($filters['admin_id'] == $admin->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($admin->full_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="action_type" class="form-select">
                                <option value="">All Actions</option>
                                <option value="lead_confirmed" <?php echo ($filters['action_type'] == 'lead_confirmed') ? 'selected' : ''; ?>>Lead Confirmed</option>
                                <option value="affiliate_update" <?php echo ($filters['action_type'] == 'affiliate_update') ? 'selected' : ''; ?>>Affiliate Update</option>
                                <option value="affiliate_status_update" <?php echo ($filters['action_type'] == 'affiliate_status_update') ? 'selected' : ''; ?>>Status Update</option>
                                <option value="sub_admin_created" <?php echo ($filters['action_type'] == 'sub_admin_created') ? 'selected' : ''; ?>>Sub-Admin Created</option>
                                <option value="sub_admin_updated" <?php echo ($filters['action_type'] == 'sub_admin_updated') ? 'selected' : ''; ?>>Sub-Admin Updated</option>
                                <option value="sub_admin_deleted" <?php echo ($filters['action_type'] == 'sub_admin_deleted') ? 'selected' : ''; ?>>Sub-Admin Deleted</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="entity_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="lead" <?php echo ($filters['entity_type'] == 'lead') ? 'selected' : ''; ?>>Lead</option>
                                <option value="affiliate" <?php echo ($filters['entity_type'] == 'affiliate') ? 'selected' : ''; ?>>Affiliate</option>
                                <option value="admin_user" <?php echo ($filters['entity_type'] == 'admin_user') ? 'selected' : ''; ?>>Admin User</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="<?php echo htmlspecialchars($filters['from_date']); ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="<?php echo htmlspecialchars($filters['to_date']); ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>

                    <!-- Logs Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Admin</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Entity</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No activity logs found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?php echo $log->id; ?></td>
                                            <td><?php echo htmlspecialchars($log->admin_name); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo ucfirst(str_replace('_', ' ', $log->action_type)); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log->action_description); ?></td>
                                            <td>
                                                <?php if ($log->entity_type && $log->entity_id): ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucfirst($log->entity_type); ?> #<?php echo $log->entity_id; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($log->ip_address); ?></td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime($log->created_at)); ?></td>
                                            <td>
                                                <?php if ($log->old_data || $log->new_data): ?>
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#logModal<?php echo $log->id; ?>">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    
                                                    <!-- Modal -->
                                                    <div class="modal fade" id="logModal<?php echo $log->id; ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Activity Details</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <h6>Old Data:</h6>
                                                                    <pre class="bg-light p-3"><?php 
                                                                        $old = json_decode($log->old_data, true);
                                                                        echo $old ? json_encode($old, JSON_PRETTY_PRINT) : 'N/A';
                                                                    ?></pre>
                                                                    <h6 class="mt-3">New Data:</h6>
                                                                    <pre class="bg-light p-3"><?php 
                                                                        $new = json_decode($log->new_data, true);
                                                                        echo $new ? json_encode($new, JSON_PRETTY_PRINT) : 'N/A';
                                                                    ?></pre>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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

                    <!-- Pagination -->
                    <?php if ($pagination['total'] > $pagination['per_page']): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php
                                $total_pages = ceil($pagination['total'] / $pagination['per_page']);
                                $current_page = $pagination['current_page'];
                                
                                // Previous
                                if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $current_page - 1])); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($filters, ['page' => $current_page + 1])); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

