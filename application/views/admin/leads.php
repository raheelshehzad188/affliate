<?php $this->load->view('layouts/header', ['title' => 'Leads']); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-shield-alt"></i> Admin</h4>
            <ul class="nav flex-column">
                <?php 
                $is_leads_only = isset($is_leads_only) ? $is_leads_only : ($this->session->userdata('admin_role') === 'leads_access'); 
                ?>
                <?php if ($is_leads_only): ?>
                <!-- Leads Access: Dashboard, Leads, Logout -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/dashboard'); ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('admin/leads'); ?>">
                        <i class="fas fa-list"></i> Leads
                    </a>
                </li>
                <?php else: ?>
                <!-- Full Access -->
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
                    <a class="nav-link active" href="<?php echo base_url('admin/leads'); ?>">
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
                    <a class="nav-link" href="<?php echo base_url('admin/activity_logs'); ?>">
                        <i class="fas fa-history"></i> Activity Logs
                    </a>
                </li>
                <?php endif; ?>
                <?php if (!$is_leads_only): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/change_password'); ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
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
                    <h5 class="mb-0"><i class="fas fa-list"></i> Leads</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="<?php echo base_url('admin/leads'); ?>" class="mb-4 row g-3">
                        <input type="hidden" name="page" value="1">
                        <div class="col-md-3">
                            <label class="form-label">Affiliate</label>
                            <select name="affiliate_id" class="form-select">
                                <option value="">All Affiliates</option>
                                <?php foreach ($affiliates as $aff): ?>
                                    <option value="<?php echo $aff->id; ?>" <?php echo (isset($filters['affiliate_id']) && $filters['affiliate_id'] == $aff->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($aff->full_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo (isset($filters['status']) && $filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo (isset($filters['status']) && $filters['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="<?php echo isset($filters['from_date']) ? $filters['from_date'] : ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="<?php echo isset($filters['to_date']) ? $filters['to_date'] : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="<?php echo base_url('admin/leads'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Leads Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Affiliate</th>
                                    <th>Discount %</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($leads): ?>
                                    <?php foreach ($leads as $lead): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lead->name); ?></td>
                                            <td><?php echo htmlspecialchars($lead->email); ?></td>
                                            <td><?php echo htmlspecialchars($lead->phone); ?></td>
                                            <td><?php echo htmlspecialchars($lead->location); ?></td>
                                            <td>
                                                <?php 
                                                $aff = null;
                                                foreach ($affiliates as $a) {
                                                    if ($a->id == $lead->affiliate_id) {
                                                        $aff = $a;
                                                        break;
                                                    }
                                                }
                                                echo $aff ? htmlspecialchars($aff->full_name) : 'N/A';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (isset($lead->discount_percent) && $lead->discount_percent !== null): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-tag"></i> <?php echo number_format($lead->discount_percent, 1); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($lead->status == 'confirmed'): ?>
                                                    <span class="badge bg-success">Confirmed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($lead->created_at)); ?></td>
                                            <td>
                                                <?php if ($lead->status == 'pending'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="confirmLead(<?php echo $lead->id; ?>)">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No leads found</td>
                                    </tr>
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
                                
                                // Build query string with filters (remove empty values)
                                $query_params = array_filter($filters, function($value) {
                                    return $value !== '' && $value !== null;
                                });
                                
                                // Previous page
                                if ($pagination['current_page'] > 1):
                                    $query_params['page'] = $pagination['current_page'] - 1;
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query($query_params); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): 
                                    $query_params['page'] = $i;
                                ?>
                                    <li class="page-item <?php echo ($pagination['current_page'] == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query($query_params); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Next page -->
                                <?php if ($pagination['current_page'] < $total_pages):
                                    $query_params['page'] = $pagination['current_page'] + 1;
                                ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query($query_params); ?>">Next</a>
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

<!-- Confirm Lead Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo base_url('admin/confirm_lead/'); ?>" id="confirmForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sale Amount</label>
                        <input type="number" name="sale_amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea name="feedback" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmLead(leadId) {
    document.getElementById('confirmForm').action = '<?php echo base_url('admin/confirm_lead/'); ?>' + leadId;
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}
</script>

<?php $this->load->view('layouts/footer'); ?>

