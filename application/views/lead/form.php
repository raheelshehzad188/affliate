<?php $this->load->view('layouts/header', ['title' => 'Contact Us']); ?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="fas fa-envelope"></i> Get In Touch</h4>
                </div>
                <div class="card-body">
                    <form id="leadForm">
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
                                <option value="Karachi">Karachi</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="detail" class="form-control" rows="4" placeholder="Tell us more about your requirements..."></textarea>
                        </div>
                        
                        <div id="alertMessage"></div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('leadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const alertDiv = document.getElementById('alertMessage');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    fetch('<?php echo base_url('lead/capture'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alertDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
            this.reset();
        } else {
            alertDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        alertDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.</div>';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit';
    });
});
</script>

<?php $this->load->view('layouts/footer'); ?>

