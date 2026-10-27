<?php $this->load->view('layouts/header', ['title' => $affiliate->full_name . ' - Affiliate']); ?>

<style>
.landing-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, #7a1f52 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}
.affiliate-profile {
    text-align: center;
    margin: -80px auto 40px;
    position: relative;
    z-index: 10;
}
.affiliate-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    object-fit: cover;
    background: #f0f0f0;
}
.affiliate-name {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 20px 0 10px;
    color: var(--primary-color);
}
.affiliate-bio {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto 40px;
}
.lead-form-section {
    background: white;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 800px;
    margin: 0 auto;
}
.stats-badge {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    margin: 10px;
    font-size: 0.9rem;
}
.discount-banner {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 20px 0;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: relative;
    z-index: 100;
    animation: slideDown 0.5s ease-out;
}
@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
.discount-banner h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
}
.discount-banner p {
    margin: 5px 0 0;
    font-size: 1.1rem;
    opacity: 0.95;
}
</style>

<?php if (isset($discount_percent) && $discount_percent !== null): ?>
<div class="discount-banner">
    <div class="container">
        <h2><i class="fas fa-tag"></i> Special Discount Offer!</h2>
        <p>You're getting <strong><?php echo number_format($discount_percent, 1); ?>% OFF</strong> - Limited Time Offer!</p>
    </div>
</div>
<?php endif; ?>

<?php 
$cover_img = !empty($affiliate->cover_image) ? $affiliate->cover_image : (isset($default_cover_image) && !empty($default_cover_image) ? $default_cover_image : '');
$cover_url = $cover_img ? (strpos($cover_img, 'http') === 0 ? $cover_img : base_url($cover_img)) : '';
?>
<div class="landing-hero" style="<?php echo $cover_url ? 'background-image: url(' . htmlspecialchars($cover_url) . '); background-size: cover; background-position: center;' : ''; ?>">
    <div class="container" style="position: relative; z-index: 1;">
        <h1 class="display-4">Welcome!</h1>
        <p class="lead">Get in touch with us through <?php echo htmlspecialchars($affiliate->full_name); ?></p>
    </div>
    <?php if ($affiliate->cover_image): ?>
        
    <?php endif; ?>
</div>

<div class="container">
    <div class="affiliate-profile">
        <?php if ($affiliate->profile_picture): ?>
            <img src="<?php echo base_url($affiliate->profile_picture); ?>" alt="<?php echo htmlspecialchars($affiliate->full_name); ?>" class="affiliate-avatar">
        <?php else: ?>
            <div class="affiliate-avatar" style="display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #999;">
                <i class="fas fa-user"></i>
            </div>
        <?php endif; ?>
        
        <h2 class="affiliate-name"><?php echo htmlspecialchars($affiliate->full_name); ?></h2>
        
        <?php if ($affiliate->bio): ?>
            <p class="affiliate-bio"><?php echo nl2br(htmlspecialchars($affiliate->bio)); ?></p>
        <?php endif; ?>
        
        <div>
            <span class="stats-badge">
                <i class="fas fa-star"></i> Trusted Affiliate
            </span>
        </div>
    </div>
    
    <div class="lead-form-section">
        <h3 class="text-center mb-4"><i class="fas fa-envelope"></i> Contact Us</h3>
        
        <?php if ($this->session->flashdata('lead_success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('lead_success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="submit_lead" value="1">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Preferred Date</label>
                    <input type="date" name="prefer_date" class="form-control">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Location</label>
                <select name="location" class="form-select">
                    <option value="">Select Location</option>
                    <option value="Lahore">Lahore</option>
                    <option value="Islamabad">Islamabad</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="detail" class="form-control" rows="4" placeholder="Tell us more about your requirements..."></textarea>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Submit Inquiry
                </button>
            </div>
        </form>
    </div>
    
    <div class="text-center mt-5 mb-5">
        <p class="text-muted">
            <small>Referred by: <strong><?php echo htmlspecialchars($affiliate->full_name); ?></strong></small>
        </p>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

