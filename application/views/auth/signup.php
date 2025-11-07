<?php $this->load->view('layouts/header', ['title' => 'Sign Up']); ?>

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Affiliate Sign Up</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('auth/signup'); ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required>
                                <small class="text-muted">Must be unique</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control" placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">How will you promote us?</label>
                            <textarea name="promote_method" class="form-control" rows="3" placeholder="Describe your marketing strategy..."></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                        
                        <?php if ($this->input->get('aff')): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You're signing up through an affiliate referral link!
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="<?php echo base_url('auth/login'); ?>">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

