<?php $this->load->view('layouts/header', ['title' => 'Affiliate Links']); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 sidebar">
            <h4 class="text-center mb-4"><i class="fas fa-link"></i> Links</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('affiliate/dashboard'); ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo base_url('affiliate/links'); ?>">
                        <i class="fas fa-link"></i> Affiliate Links
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Your Affiliate Links</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Landing Page Link</label>
                        <div class="input-group">
                            <input type="text" id="profileLink" class="form-control" value="<?php echo $profile_link; ?>" readonly>
                            <button class="btn btn-primary" onclick="copyLink('profileLink')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Your unique landing page: <strong><?php echo $affiliate->slug; ?></strong></small>
                        <p class="text-info mt-2"><i class="fas fa-info-circle"></i> Share this link to track clicks and capture leads</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Signup Link</label>
                        <div class="input-group">
                            <input type="text" id="signupLink" class="form-control" value="<?php echo $signup_link; ?>" readonly>
                            <button class="btn btn-primary" onclick="copyLink('signupLink')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Share this link for new affiliate signups</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Tips:</strong> Share these links on your website, social media, or email campaigns to earn commissions.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyLink(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}
</script>

<?php $this->load->view('layouts/footer'); ?>

