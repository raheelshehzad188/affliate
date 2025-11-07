<?php $this->load->view('layouts/header', ['title' => 'Admin Login', 'no_navbar' => true]); ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-center bg-danger">
                    <h4 class="mb-0"><i class="fas fa-shield-alt"></i> Admin Login</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('admin/login'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Admin Login
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p><a href="<?php echo base_url('auth/login'); ?>">Affiliate Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

