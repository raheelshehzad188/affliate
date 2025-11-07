<?php $this->load->view('layouts/header', ['title' => 'Affiliate Detail']); ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 20px;">
        <?php echo $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px;">
        <?php echo $this->session->flashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-shield-alt"></i> Admin</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('admin/affiliates'); ?>">
                        <i class="fas fa-arrow-left"></i> Back to Affiliates
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="col-md-9">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Affiliate Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('admin/affiliate_detail/' . $affiliate->id); ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($affiliate->full_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($affiliate->email); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" <?php echo ($affiliate->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="active" <?php echo ($affiliate->status == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($affiliate->status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($affiliate->website); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($affiliate->slug); ?>" placeholder="affiliate-slug">
                                <small class="text-muted">Unique URL slug for landing page (e.g., john-doe)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave empty to keep current password">
                                <small class="text-muted">Minimum 6 characters. Leave empty if you don't want to change it.</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_special" id="is_special" value="1" <?php echo (isset($affiliate->is_special) && $affiliate->is_special == 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_special">
                                        <strong>Special Affiliate</strong> - Allow affiliate to change banner image from their profile
                                    </label>
                                    <small class="text-muted d-block">Note: Admin can always change banner image regardless of this setting</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Picture</label>
                                <?php if ($affiliate->profile_picture): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo base_url($affiliate->profile_picture); ?>" alt="Profile" class="img-thumbnail" style="max-width: 150px; border-radius: 50%;">
                                        <br><small class="text-muted">Current profile picture</small>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-2">
                                        <div style="width: 150px; height: 150px; border: 2px dashed #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                                            <i class="fas fa-user fa-3x text-muted"></i>
                                        </div>
                                        <small class="text-muted">No profile picture</small>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="profile_picture" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended: Square image (150x150px or larger)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cover/Banner Image</label>
                                <?php if ($affiliate->cover_image): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo base_url($affiliate->cover_image); ?>" alt="Cover" class="img-thumbnail" style="max-width: 100%; max-height: 200px; border-radius: 10px;">
                                        <br><small class="text-muted">Current banner image</small>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-2" style="background: linear-gradient(135deg, #5C163D 0%, #7a1f52 100%); padding: 40px; border-radius: 10px; text-align: center; color: white;">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="mb-0" style="font-size: 0.9rem;">No banner image</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended: 1200x400px or larger. Admin can always change this.</small>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Affiliate
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $stats['clicks']; ?></h3>
                                <p>Clicks</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $stats['total_leads']; ?></h3>
                                <p>Total Leads</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3><?php echo $stats['confirmed_leads']; ?></h3>
                                <p>Confirmed Leads</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <h3>$<?php echo number_format($stats['total_commission'], 2); ?></h3>
                                <p>Commission</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

