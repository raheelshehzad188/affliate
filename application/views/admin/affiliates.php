<?php $this->load->view('layouts/header', ['title' => 'Affiliates']); ?>

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
                    <a class="nav-link active" href="<?php echo base_url('admin/affiliates'); ?>">
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
                    <a class="nav-link" href="<?php echo base_url('admin/change_password'); ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Affiliates</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4 row g-3">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="active" <?php echo ($filters['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($filters['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo $filters['search']; ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from_date" class="form-control" value="<?php echo $filters['from_date']; ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" class="form-control" value="<?php echo $filters['to_date']; ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                    
                    <!-- Affiliates Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Website</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($affiliates): ?>
                                    <?php foreach ($affiliates as $affiliate): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($affiliate->full_name); ?></td>
                                            <td><?php echo htmlspecialchars($affiliate->email); ?></td>
                                            <td><?php echo htmlspecialchars($affiliate->website); ?></td>
                                            <td>
                                                <?php if ($affiliate->status == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php elseif ($affiliate->status == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($affiliate->created_at)); ?></td>
                                            <td>
                                                <a href="<?php echo base_url('admin/affiliate_detail/' . $affiliate->id); ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No affiliates found</td>
                                    </tr>
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

