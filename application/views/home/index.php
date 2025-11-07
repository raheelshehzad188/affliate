<?php $this->load->view('layouts/header', ['title' => 'Home']); ?>

<div class="container">
    <div class="jumbotron bg-primary text-white text-center py-5 rounded mb-5">
        <h1 class="display-4">Welcome to Affiliate System</h1>
        <p class="lead">Join our affiliate program and start earning commissions today!</p>
        <a href="<?php echo base_url('auth/signup'); ?>" class="btn btn-light btn-lg mt-3">
            <i class="fas fa-user-plus"></i> Become an Affiliate
        </a>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-dollar-sign fa-3x text-primary mb-3"></i>
                    <h5>Earn Commissions</h5>
                    <p>Get paid for every lead you refer. Multi-level commission structure available.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                    <h5>Track Performance</h5>
                    <p>Monitor your clicks, leads, and commissions in real-time dashboard.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-link fa-3x text-info mb-3"></i>
                    <h5>Easy Sharing</h5>
                    <p>Get your unique affiliate links and start sharing with your audience.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-envelope"></i> Contact Us</h5>
        </div>
        <div class="card-body">
            <p>Interested in our services? Fill out the form below:</p>
            <a href="<?php echo base_url('lead/capture'); ?>" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Contact Form
            </a>
        </div>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

