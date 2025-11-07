<?php $this->load->view('layouts/header', ['title' => 'Commissions']); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-shield-alt"></i> Admin</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('admin/commissions'); ?>">
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
                    <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Commissions</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4 row g-3">
                        <div class="col-md-3">
                            <select name="affiliate_id" class="form-select">
                                <option value="">All Affiliates</option>
                                <?php foreach ($affiliates as $aff): ?>
                                    <option value="<?php echo $aff->id; ?>" <?php echo ($filters['affiliate_id'] == $aff->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($aff->full_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo ($filters['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="paid" <?php echo ($filters['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="from_date" class="form-control" value="<?php echo $filters['from_date']; ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="to_date" class="form-control" value="<?php echo $filters['to_date']; ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                    
                    <!-- Commissions Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Affiliate</th>
                                    <th>Lead ID</th>
                                    <th>Total Sale</th>
                                    <th>Commission</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($commissions): ?>
                                    <?php foreach ($commissions as $commission): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $aff = null;
                                                foreach ($affiliates as $a) {
                                                    if ($a->id == $commission->affiliate_id) {
                                                        $aff = $a;
                                                        break;
                                                    }
                                                }
                                                echo $aff ? htmlspecialchars($aff->full_name) : 'N/A';
                                                ?>
                                            </td>
                                            <td>#<?php echo $commission->lead_id; ?></td>
                                            <td>$<?php echo number_format($commission->total_sale, 2); ?></td>
                                            <td class="text-success"><strong>$<?php echo number_format($commission->commission_amount, 2); ?></strong></td>
                                            <td><span class="badge bg-info">Level <?php echo $commission->level; ?></span></td>
                                            <td>
                                                <?php if ($commission->status == 'confirmed'): ?>
                                                    <span class="badge bg-success">Confirmed</span>
                                                <?php elseif ($commission->status == 'paid'): ?>
                                                    <span class="badge bg-primary">Paid</span>
                                                <?php elseif ($commission->status == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($commission->created_at)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No commissions found</td>
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

