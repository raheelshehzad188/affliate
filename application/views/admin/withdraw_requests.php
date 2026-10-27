<?php $this->load->view('layouts/header', ['title' => 'Withdraw Requests']); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/dashboard'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?php echo base_url('admin/withdraw_requests'); ?>"><i class="fas fa-money-bill-wave"></i> Withdraw Requests</a></li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Withdraw Requests</h5></div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <select name="status" class="form-select d-inline-block w-auto">
                            <option value="">All</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status']=='pending')?'selected':''; ?>>Pending</option>
                            <option value="approved" <?php echo (isset($_GET['status']) && $_GET['status']=='approved')?'selected':''; ?>>Approved</option>
                            <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status']=='rejected')?'selected':''; ?>>Rejected</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Affiliate</th><th>Amount</th><th>Bank</th><th>Account</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r->full_name ?? '-'); ?><br><small><?php echo htmlspecialchars($r->email ?? ''); ?></small></td>
                                <td><?php echo isset($r->amount) ? number_format($r->amount, 2) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($r->bank_name ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($r->account_number ?? '-'); ?></td>
                                <td><span class="badge bg-<?php echo $r->status=='approved'?'success':($r->status=='rejected'?'danger':'warning'); ?>"><?php echo $r->status; ?></span></td>
                                <td><?php echo $r->created_at ?? '-'; ?></td>
                                <td>
                                    <?php if ($r->status == 'pending'): ?>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $r->id; ?>">Approve</button>
                                    <form method="POST" action="<?php echo base_url('admin/reject_withdraw/'.$r->id); ?>" class="d-inline">
                                        <input type="text" name="admin_notes" placeholder="Reason" class="form-control form-control-sm d-inline-block w-auto">
                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                    <!-- Approve Modal -->
                                    <div class="modal fade" id="approveModal<?php echo $r->id; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Approve Withdraw - <?php echo htmlspecialchars($r->full_name ?? ''); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="<?php echo base_url('admin/approve_withdraw/'.$r->id); ?>">
                                                    <div class="modal-body">
                                                        <p class="mb-2"><strong>Bank:</strong> <?php echo htmlspecialchars($r->bank_name ?? '-'); ?></p>
                                                        <p class="mb-2"><strong>Account:</strong> <?php echo htmlspecialchars($r->account_number ?? '-'); ?></p>
                                                        <p class="mb-3"><strong>Requested:</strong> <?php echo isset($r->amount) ? number_format($r->amount, 2) : '0'; ?></p>
                                                        <div class="mb-3">
                                                            <label class="form-label">Withdraw Amount <small class="text-muted">(add kar sakte hain)</small></label>
                                                            <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?php echo isset($r->amount) ? htmlspecialchars($r->amount) : ''; ?>" placeholder="Amount" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Approve</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($requests)): ?>
                            <tr><td colspan="7" class="text-center">No withdraw requests</td></tr>
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
