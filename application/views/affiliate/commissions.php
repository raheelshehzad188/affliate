<?php $this->load->view('layouts/header', ['title' => 'Commissions']); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-dollar-sign"></i> Commissions</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/dashboard'); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('affiliate/commissions'); ?>">
                        <i class="fas fa-dollar-sign"></i> Commissions
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> All Commissions</h5>
                </div>
                <div class="card-body">
                    <?php if ($commissions): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Lead ID</th>
                                        <th>Total Sale</th>
                                        <th>Commission</th>
                                        <th>Percent</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($commissions as $commission): ?>
                                        <tr>
                                            <td>#<?php echo $commission->lead_id; ?></td>
                                            <td>$<?php echo number_format($commission->total_sale, 2); ?></td>
                                            <td class="text-success"><strong>$<?php echo number_format($commission->commission_amount, 2); ?></strong></td>
                                            <td><?php echo $commission->commission_percent; ?>%</td>
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
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No commissions yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

