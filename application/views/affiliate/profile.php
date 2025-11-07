<?php $this->load->view('layouts/header', ['title' => 'Edit Profile']); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-user"></i> Profile</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/dashboard'); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('affiliate/profile'); ?>">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/change_password'); ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('affiliate/profile'); ?>" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($affiliate->full_name); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($affiliate->username); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($affiliate->email); ?>" disabled>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($affiliate->website); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($affiliate->bio); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">HubSpot Token</label>
                            <input type="text" name="hubspot_token" class="form-control" value="<?php echo htmlspecialchars($affiliate->hubspot_token); ?>" placeholder="Enter your HubSpot API token">
                            <small class="text-muted">Optional: For automatic lead syncing</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <?php if ($affiliate->profile_picture): ?>
                                <div class="mb-2">
                                    <img src="<?php echo base_url($affiliate->profile_picture); ?>" alt="Profile" class="img-thumbnail" style="max-width: 150px; border-radius: 50%;">
                                </div>
                            <?php else: ?>
                                <div class="mb-2">
                                    <div style="width: 150px; height: 150px; border: 2px dashed #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended: Square image (150x150px or larger)</small>
                        </div>
                        
                        <?php if (isset($affiliate->is_special) && $affiliate->is_special == 1): ?>
                            <div class="mb-3">
                                <label class="form-label">Cover/Banner Image</label>
                                <?php if ($affiliate->cover_image): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo base_url($affiliate->cover_image); ?>" alt="Cover" class="img-thumbnail" style="max-width: 100%; max-height: 200px; border-radius: 10px;">
                                        <br><small class="text-muted">Current cover image</small>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-2" style="background: linear-gradient(135deg, #5C163D 0%, #7a1f52 100%); padding: 40px; border-radius: 10px; text-align: center; color: white;">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="mb-0">No cover image uploaded</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended: 1200x400px or larger (will be displayed on your landing page)</small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

