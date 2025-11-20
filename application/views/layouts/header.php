<?php
// Load settings if not already loaded
if (!isset($settings)) {
    $settings = [];
    // Try to load settings model safely
    if (file_exists(APPPATH . 'models/Settings_model.php')) {
        $CI =& get_instance();
        if (!isset($CI->Settings_model)) {
            $CI->load->model('Settings_model');
        }
        if (isset($CI->Settings_model) && is_object($CI->Settings_model)) {
            $settings = $CI->Settings_model->get_all();
            if (!is_array($settings)) {
                $settings = [];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : (isset($settings['site_name']) ? $settings['site_name'] : 'Affiliate System'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5C163D;
            --secondary-color: #C68F4E;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a1f52 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand img {
            height: 40px;
            width: auto;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #7a1f52;
            border-color: #7a1f52;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #7a1f52 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            padding: 20px 0;
            color: white;
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        /* Mobile Responsive */
        @media (max-width: 767.98px) {
            .sidebar {
                display: none !important;
            }
            .col-md-9 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
            .col-md-3 {
                width: 100% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
            .stat-card {
                padding: 15px;
                margin-bottom: 15px;
            }
            .stat-card h3 {
                font-size: 1.8rem;
            }
            .stat-card i {
                font-size: 1.5rem !important;
            }
            .container-fluid {
                padding-left: 15px;
                padding-right: 15px;
            }
            .table-responsive {
                font-size: 0.85rem;
            }
            .table th,
            .table td {
                padding: 8px 4px;
            }
            .card-body {
                padding: 15px;
            }
            .card-header h5 {
                font-size: 1rem;
            }
            h2 {
                font-size: 1.5rem;
            }
            .navbar-nav .nav-link {
                padding: 10px 15px;
            }
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }
        .stat-card p {
            color: #666;
            margin: 0;
        }
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (!isset($no_navbar) || !$no_navbar): ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo base_url(); ?>">
                <?php if (isset($settings['site_logo']) && !empty($settings['site_logo']) && $settings['site_logo'] != '#'): ?>
                    <img src="<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="<?php echo isset($settings['site_name']) ? htmlspecialchars($settings['site_name']) : 'Logo'; ?>" class="navbar-logo">
                <?php endif; ?>
                <span><?php echo isset($settings['site_name']) ? htmlspecialchars($settings['site_name']) : '<i class="fas fa-handshake"></i> Affiliate System'; ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($this->session->userdata('affiliate_id')): ?>
                        <!-- Mobile Menu Items (show on mobile, hide on desktop where sidebar is visible) -->
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="<?php echo base_url('affiliate/dashboard'); ?>">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="<?php echo base_url('affiliate/commissions'); ?>">
                                <i class="fas fa-dollar-sign"></i> Commissions
                            </a>
                        </li>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="<?php echo base_url('affiliate/links'); ?>">
                                <i class="fas fa-link"></i> Affiliate Links
                            </a>
                        </li>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="<?php echo base_url('affiliate/profile'); ?>">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="<?php echo base_url('affiliate/change_password'); ?>">
                                <i class="fas fa-key"></i> Change Password
                            </a>
                        </li>
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="<?php echo base_url('auth/logout'); ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                        <!-- Desktop: Only show Logout in top menu (sidebar has other items) -->
                        <li class="nav-item d-none d-md-block">
                            <a class="nav-link" href="<?php echo base_url('auth/logout'); ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php elseif ($this->session->userdata('admin_id')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('admin/dashboard'); ?>">
                                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('admin/logout'); ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('auth/login'); ?>">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('auth/signup'); ?>">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

