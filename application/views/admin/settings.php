<?php $this->load->view('layouts/header', ['title' => 'Settings']); ?>

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
                    <a class="nav-link active" href="<?php echo base_url('admin/settings'); ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
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
            <h2 class="mb-4"><i class="fas fa-cog"></i> Settings</h2>
            
            <?php if (isset($table_exists) && !$table_exists): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Settings Table Not Found</h5>
                <p>The settings table does not exist in the database. Please run the table creation script:</p>
                <ol>
                    <li>Open your browser and go to: <code><?php echo base_url('create_settings_table.php'); ?></code></li>
                    <li>Or run it via command line: <code>php create_settings_table.php</code></li>
                </ol>
                <p class="mb-0">Once the table is created, refresh this page.</p>
            </div>
            <?php endif; ?>
            
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($active_tab == 'general') ? 'active' : ''; ?>" 
                            id="general-tab" data-bs-toggle="tab" data-bs-target="#general" 
                            type="button" role="tab" aria-controls="general" aria-selected="<?php echo ($active_tab == 'general') ? 'true' : 'false'; ?>">
                        <i class="fas fa-info-circle"></i> General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($active_tab == 'homepage') ? 'active' : ''; ?>" 
                            id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" 
                            type="button" role="tab" aria-controls="homepage" aria-selected="<?php echo ($active_tab == 'homepage') ? 'true' : 'false'; ?>">
                        <i class="fas fa-home"></i> Homepage
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($active_tab == 'contact') ? 'active' : ''; ?>" 
                            id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" 
                            type="button" role="tab" aria-controls="contact" aria-selected="<?php echo ($active_tab == 'contact') ? 'true' : 'false'; ?>">
                        <i class="fas fa-phone"></i> Contact
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($active_tab == 'social') ? 'active' : ''; ?>" 
                            id="social-tab" data-bs-toggle="tab" data-bs-target="#social" 
                            type="button" role="tab" aria-controls="social" aria-selected="<?php echo ($active_tab == 'social') ? 'true' : 'false'; ?>">
                        <i class="fas fa-share-alt"></i> Social Media
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($active_tab == 'footer') ? 'active' : ''; ?>" 
                            id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer" 
                            type="button" role="tab" aria-controls="footer" aria-selected="<?php echo ($active_tab == 'footer') ? 'true' : 'false'; ?>">
                        <i class="fas fa-window-minimize"></i> Footer
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo ($active_tab == 'commission') ? 'active' : ''; ?>" 
                            id="commission-tab" data-bs-toggle="tab" data-bs-target="#commission" 
                            type="button" role="tab" aria-controls="commission" aria-selected="<?php echo ($active_tab == 'commission') ? 'true' : 'false'; ?>">
                        <i class="fas fa-percent"></i> Commission
                    </button>
                </li>
            </ul>
            
            <!-- Tabs Content -->
            <div class="tab-content" id="settingsTabsContent">
                
                <!-- General Settings Tab -->
                <div class="tab-pane fade <?php echo ($active_tab == 'general') ? 'show active' : ''; ?>" 
                     id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo base_url('admin/settings?tab=general'); ?>">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Site Name</label>
                                        <input type="text" name="site_name" class="form-control" 
                                               value="<?php echo isset($settings['site_name']) ? htmlspecialchars($settings['site_name']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Site Tagline</label>
                                        <input type="text" name="site_tagline" class="form-control" 
                                               value="<?php echo isset($settings['site_tagline']) ? htmlspecialchars($settings['site_tagline']) : ''; ?>">
                                        <small class="form-text text-muted">Shows in homepage top bar</small>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Site Logo URL</label>
                                        <input type="url" name="site_logo" class="form-control" 
                                               value="<?php echo isset($settings['site_logo']) ? htmlspecialchars($settings['site_logo']) : ''; ?>" 
                                               placeholder="https://example.com/logo.png">
                                        <small class="form-text text-muted">Logo will appear in navbar. Leave empty to use text only.</small>
                                        <?php if (isset($settings['site_logo']) && !empty($settings['site_logo']) && $settings['site_logo'] != '#'): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Logo Preview" style="max-height: 50px; max-width: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Site Email</label>
                                        <input type="email" name="site_email" class="form-control" 
                                               value="<?php echo isset($settings['site_email']) ? htmlspecialchars($settings['site_email']) : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Site Phone</label>
                                        <input type="text" name="site_phone" class="form-control" 
                                               value="<?php echo isset($settings['site_phone']) ? htmlspecialchars($settings['site_phone']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Credit Amount (Affiliate Game)</label>
                                        <input type="number" name="credit_amount" class="form-control" 
                                               value="<?php echo isset($settings['credit_amount']) ? htmlspecialchars($settings['credit_amount']) : '100000'; ?>" 
                                               min="0" step="0.01" placeholder="100000">
                                        <small class="form-text text-muted">Sab affiliates ko same amount dikhega. Commission earn hone par ghata jayega.</small>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">Default Affiliate Cover Image</label>
                                        <input type="text" name="default_cover_image" class="form-control" 
                                               value="<?php echo isset($settings['default_cover_image']) ? htmlspecialchars($settings['default_cover_image']) : ''; ?>" 
                                               placeholder="uploads/cover/default.jpg ya full URL (e.g. https://example.com/banner.jpg)">
                                        <small class="form-text text-muted">Jab affiliate ke paas koi custom cover nahi ho (admin ne set nahi kiya), to yeh default cover dikhega. Path: <code>uploads/cover/default.jpg</code> ya full URL.</small>
                                        <?php if (isset($settings['default_cover_image']) && !empty($settings['default_cover_image'])): ?>
                                        <div class="mt-2">
                                            <?php 
                                            $preview_url = (strpos($settings['default_cover_image'], 'http') === 0) 
                                                ? $settings['default_cover_image'] 
                                                : base_url($settings['default_cover_image']); 
                                            ?>
                                            <img src="<?php echo htmlspecialchars($preview_url); ?>" alt="Default Cover Preview" style="max-height: 80px; max-width: 300px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;" onerror="this.style.display='none'">
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save General Settings
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <!-- Refresh Data Section -->
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Refresh Data</h6>
                                <p class="mb-2">This will delete all leads and commissions data. <strong>Users and Settings will be preserved.</strong></p>
                                <p class="mb-3 text-danger"><strong>⚠️ Warning: This action cannot be undone!</strong></p>
                                <form method="POST" action="<?php echo base_url('admin/refresh_data'); ?>" onsubmit="return confirm('Are you sure you want to delete all leads and commissions? This action cannot be undone!');">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-sync-alt"></i> Refresh Data (Delete Leads & Commissions)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Homepage Settings Tab (New Design) -->
                <div class="tab-pane fade <?php echo ($active_tab == 'homepage') ? 'show active' : ''; ?>" 
                     id="homepage" role="tabpanel" aria-labelledby="homepage-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-home"></i> Homepage Settings</h5>
                            <small class="text-muted d-block mt-1">Controls the new landing page design (banner, video, who can join, etc.)</small>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo base_url('admin/settings?tab=homepage'); ?>">
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle"></i> <strong>Top Bar text</strong> uses <strong>Site Tagline</strong> from General tab.
                                </div>
                                <hr>
                                <h6><i class="fas fa-image"></i> Banner Section</h6>
                                <div class="mb-3">
                                    <label class="form-label">Banner Image URL</label>
                                    <input type="url" name="banner_image" class="form-control" 
                                           value="<?php echo isset($settings['banner_image']) ? htmlspecialchars($settings['banner_image']) : ''; ?>" 
                                           placeholder="https://example.com/affiliate-banner.jpg">
                                    <small class="form-text text-muted">Full-width banner below navbar</small>
                                </div>
                                <hr>
                                <h6><i class="fab fa-youtube"></i> Video Section</h6>
                                <div class="mb-3">
                                    <label class="form-label">YouTube Video ID or URL</label>
                                    <input type="text" name="video_url" class="form-control" 
                                           value="<?php echo isset($settings['video_url']) ? htmlspecialchars($settings['video_url']) : ''; ?>" 
                                           placeholder="YOURVIDEO or https://www.youtube.com/watch?v=YOURVIDEO">
                                    <small class="form-text text-muted">YouTube video ID (e.g. dQw4w9WgXcQ) or full URL</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Video Description (text below video)</label>
                                    <textarea name="video_description" class="form-control" rows="3"><?php echo isset($settings['video_description']) ? htmlspecialchars($settings['video_description']) : ''; ?></textarea>
                                    <small class="form-text text-muted">Short description of the affiliate program</small>
                                </div>
                                <hr>
                                <h6><i class="fas fa-users"></i> Who Can Join Section</h6>
                                <div class="mb-3">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="who_can_join_title" class="form-control" 
                                           value="<?php echo isset($settings['who_can_join_title']) ? htmlspecialchars($settings['who_can_join_title']) : ''; ?>" 
                                           placeholder="Who Can Join?">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Section Description</label>
                                    <textarea name="who_can_join_desc" class="form-control" rows="3"><?php echo isset($settings['who_can_join_desc']) ? htmlspecialchars($settings['who_can_join_desc']) : ''; ?></textarea>
                                    <small class="form-text text-muted">Main paragraph with bold keywords</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Info Strip Text</label>
                                    <input type="text" name="who_can_join_info" class="form-control" 
                                           value="<?php echo isset($settings['who_can_join_info']) ? htmlspecialchars($settings['who_can_join_info']) : ''; ?>" 
                                           placeholder="No prior experience required! We provide you with the tools and support you need.">
                                    <small class="form-text text-muted">Gold info box text</small>
                                </div>
                                <hr>
                                <h6><i class="fas fa-bars"></i> Navbar & Features</h6>
                                <div class="mb-3">
                                    <label class="form-label">Navbar Center Text</label>
                                    <input type="text" name="navbar_center_text" class="form-control" 
                                           value="<?php echo isset($settings['navbar_center_text']) ? htmlspecialchars($settings['navbar_center_text']) : ''; ?>" 
                                           placeholder="Affiliate Dashboard">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Feature Section Title</label>
                                    <input type="text" name="feature_section_title" class="form-control" 
                                           value="<?php echo isset($settings['feature_section_title']) ? htmlspecialchars($settings['feature_section_title']) : ''; ?>" 
                                           placeholder="Earn. Grow. Empower. Repeat.">
                                    <small class="form-text text-muted">Title above Earn/Grow/Empower/Repeat boxes</small>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Homepage Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Settings Tab -->
                <div class="tab-pane fade <?php echo ($active_tab == 'contact') ? 'show active' : ''; ?>" 
                     id="contact" role="tabpanel" aria-labelledby="contact-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-phone"></i> Contact Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo base_url('admin/settings?tab=contact'); ?>">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" name="contact_phone" class="form-control" 
                                               value="<?php echo isset($settings['contact_phone']) ? htmlspecialchars($settings['contact_phone']) : ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" name="contact_email" class="form-control" 
                                               value="<?php echo isset($settings['contact_email']) ? htmlspecialchars($settings['contact_email']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact Address</label>
                                    <textarea name="contact_address" class="form-control" rows="2"><?php echo isset($settings['contact_address']) ? htmlspecialchars($settings['contact_address']) : ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Working Hours</label>
                                    <input type="text" name="working_hours" class="form-control" 
                                           value="<?php echo isset($settings['working_hours']) ? htmlspecialchars($settings['working_hours']) : ''; ?>">
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Contact Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media Settings Tab -->
                <div class="tab-pane fade <?php echo ($active_tab == 'social') ? 'show active' : ''; ?>" 
                     id="social" role="tabpanel" aria-labelledby="social-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-share-alt"></i> Social Media Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo base_url('admin/settings?tab=social'); ?>">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fab fa-facebook"></i> Facebook URL</label>
                                        <input type="url" name="social_facebook" class="form-control" 
                                               value="<?php echo isset($settings['social_facebook']) ? htmlspecialchars($settings['social_facebook']) : ''; ?>">
                                        <small class="form-text text-muted">Homepage footer</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fab fa-instagram"></i> Instagram URL</label>
                                        <input type="url" name="social_instagram" class="form-control" 
                                               value="<?php echo isset($settings['social_instagram']) ? htmlspecialchars($settings['social_instagram']) : ''; ?>">
                                        <small class="form-text text-muted">Homepage footer</small>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fab fa-youtube"></i> YouTube URL</label>
                                        <input type="url" name="social_youtube" class="form-control" 
                                               value="<?php echo isset($settings['social_youtube']) ? htmlspecialchars($settings['social_youtube']) : ''; ?>">
                                        <small class="form-text text-muted">Homepage footer</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fab fa-linkedin"></i> LinkedIn URL</label>
                                        <input type="url" name="social_linkedin" class="form-control" 
                                               value="<?php echo isset($settings['social_linkedin']) ? htmlspecialchars($settings['social_linkedin']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fab fa-twitter"></i> Twitter/X URL</label>
                                        <input type="url" name="social_twitter" class="form-control" 
                                               value="<?php echo isset($settings['social_twitter']) ? htmlspecialchars($settings['social_twitter']) : ''; ?>">
                                    </div>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Social Media Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Footer Settings Tab -->
                <div class="tab-pane fade <?php echo ($active_tab == 'footer') ? 'show active' : ''; ?>" 
                     id="footer" role="tabpanel" aria-labelledby="footer-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-window-minimize"></i> Footer Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo base_url('admin/settings?tab=footer'); ?>">
                                <div class="mb-3">
                                    <label class="form-label">Footer Description</label>
                                    <textarea name="footer_text" class="form-control" rows="3"><?php echo isset($settings['footer_text']) ? htmlspecialchars($settings['footer_text']) : ''; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Copyright Text</label>
                                    <input type="text" name="footer_copyright" class="form-control" 
                                           value="<?php echo isset($settings['footer_copyright']) ? htmlspecialchars($settings['footer_copyright']) : ''; ?>">
                                    <small class="form-text text-muted">Use {year} to display current year automatically</small>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Footer Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Commission Settings Tab -->
                <div class="tab-pane fade <?php echo ($active_tab == 'commission') ? 'show active' : ''; ?>" 
                     id="commission" role="tabpanel" aria-labelledby="commission-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-percent"></i> Commission Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo base_url('admin/settings?tab=commission'); ?>">
                                <h6 class="mb-3"><i class="fas fa-dollar-sign"></i> Currency Settings</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Currency Symbol</label>
                                        <input type="text" name="currency_symbol" class="form-control" 
                                               value="<?php echo isset($settings['currency_symbol']) ? htmlspecialchars($settings['currency_symbol']) : '$'; ?>" 
                                               maxlength="5" placeholder="$">
                                        <small class="form-text text-muted">Currency symbol (e.g., $, £, €, ₹)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Currency Code</label>
                                        <input type="text" name="currency_code" class="form-control" 
                                               value="<?php echo isset($settings['currency_code']) ? htmlspecialchars($settings['currency_code']) : 'USD'; ?>" 
                                               maxlength="3" placeholder="USD">
                                        <small class="form-text text-muted">ISO currency code (e.g., USD, GBP, EUR, INR)</small>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <h6 class="mb-3"><i class="fas fa-percent"></i> Commission Settings</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Default Commission Rate (%)</label>
                                        <input type="number" name="commission_rate" class="form-control" 
                                               value="<?php echo isset($settings['commission_rate']) ? htmlspecialchars($settings['commission_rate']) : '22'; ?>" 
                                               min="0" max="100" step="0.1">
                                        <small class="form-text text-muted">Default commission percentage for affiliates</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Minimum Payout Amount</label>
                                        <input type="number" name="min_payout" class="form-control" 
                                               value="<?php echo isset($settings['min_payout']) ? htmlspecialchars($settings['min_payout']) : '50'; ?>" 
                                               min="0" step="0.01">
                                        <small class="form-text text-muted">Minimum amount required for payout</small>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <h6 class="mb-3"><i class="fas fa-tag"></i> Discount Settings</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Minimum Discount %</label>
                                        <input type="number" name="discount_min" class="form-control" 
                                               value="<?php echo isset($settings['discount_min']) ? htmlspecialchars($settings['discount_min']) : '0'; ?>" 
                                               min="0" max="100" step="0.1">
                                        <small class="form-text text-muted">Minimum discount percentage affiliates can offer</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Maximum Discount %</label>
                                        <input type="number" name="discount_max" class="form-control" 
                                               value="<?php echo isset($settings['discount_max']) ? htmlspecialchars($settings['discount_max']) : '50'; ?>" 
                                               min="0" max="100" step="0.1">
                                        <small class="form-text text-muted">Maximum discount percentage affiliates can offer</small>
                                    </div>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Commission Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handle tab switching with URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        const tabElement = document.getElementById(tab + '-tab');
        if (tabElement) {
            const tabTrigger = new bootstrap.Tab(tabElement);
            tabTrigger.show();
        }
    }
</script>

<?php $this->load->view('layouts/footer'); ?>

