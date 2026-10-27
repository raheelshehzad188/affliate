<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($settings['site_name']) ? htmlspecialchars($settings['site_name']) : 'London Aesthetics UK'; ?> - Affiliate Program</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-purple: #6a1a4c;
            --dark-purple: #4a1135;
            --gold-text: #c69058;
            --gold-bg: #d7a66b;
            --light-gray: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: #333;
        }

        /* Top Bar */
        .topbar {
            background: var(--primary-purple);
            color: white;
            padding: 8px;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Navbar */
        .navbar {
            padding: 15px 5%;
            background: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn-gold-outline {
            border: 1px solid var(--gold-bg);
            color: var(--gold-bg);
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn-gold-outline:hover {
            background: var(--gold-bg);
            color: white;
        }

        /* Hero Banner */
        .banner-img {
            width: 100%;
            border-radius: 0;
        }

        /* Titles */
        .section-title {
            color: var(--gold-text);
            font-weight: 700;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        /* Step Cards */
        .start-card {
            border: none;
            border-radius: 15px;
            padding: 40px 20px;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            height: 100%;
            transition: transform 0.3s;
        }
        .start-card:hover {
            transform: translateY(-10px);
        }
        .start-card i {
            font-size: 40px;
            color: #888;
            margin-bottom: 20px;
        }
        .start-card h5 {
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Buttons */
        .btn-main {
            background: var(--dark-purple);
            color: white;
            padding: 12px 40px;
            border-radius: 5px;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
        }
        .btn-main:hover {
            background: var(--primary-purple);
            color: white;
        }

        /* Alert/Who Can Join */
        .info-strip {
            background: var(--gold-bg);
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Form Boxes */
        .form-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            border: 1px solid #ddd;
            height: 100%;
        }
        .form-control {
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
        }

        /* Bottom Feature Icons */
        .feature-box {
            background: #fff;
            padding: 30px 10px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        /* Footer */
        .footer-main {
            background: #fff;
            padding: 60px 0 20px;
            border-top: 1px solid #eee;
        }
        .footer-title {
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .social-icons a {
            font-size: 20px;
            margin: 0 10px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="topbar text-center">
    <?php echo isset($settings['site_tagline']) && $settings['site_tagline'] ? htmlspecialchars($settings['site_tagline']) : 'LET YOUR SKIN SHINE BRIGHTER WITH LONDON AESTHETICS UK'; ?>
</div>

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a href="<?php echo base_url(); ?>"><img src="<?php echo (isset($settings['site_logo']) && !empty($settings['site_logo']) && $settings['site_logo'] != '#') ? htmlspecialchars($settings['site_logo']) : 'https://affiliates.londonaesthetics.com.pk/wp-content/uploads/2025/06/Logo-scaled-1.png'; ?>" height="50" alt="Logo"></a>
        <h5 class="m-auto d-none d-md-block" style="color: var(--primary-purple); font-weight: 600;"><?php echo isset($settings['navbar_center_text']) && $settings['navbar_center_text'] ? htmlspecialchars($settings['navbar_center_text']) : 'Affiliate Dashboard'; ?></h5>
        <a href="<?php echo base_url(); ?>#forms-section" class="btn btn-gold-outline">Affiliate Login →</a>
    </div>
</nav>

<?php $banner_img = (isset($settings['banner_image']) && !empty($settings['banner_image'])) ? $settings['banner_image'] : 'https://londonaesthetics.com.pk/wp-content/uploads/2025/07/Affiliate-Banner-1-1-1-1-1-1.jpg'; ?>
<img src="<?php echo htmlspecialchars($banner_img); ?>" class="banner-img" alt="Banner">

<section class="container my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php 
            $video_id = '';
            if (isset($settings['video_url']) && !empty($settings['video_url'])) {
                $v = trim($settings['video_url']);
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $v, $m)) {
                    $video_id = $m[1];
                } elseif (preg_match('/^[a-zA-Z0-9_-]{10,}$/', $v)) {
                    $video_id = $v;
                }
            }
            $video_url = $video_id ? 'https://www.youtube.com/embed/' . $video_id : '';
            ?>
            <?php if ($video_url): ?>
            <div class="ratio ratio-16x9 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <iframe src="<?php echo htmlspecialchars($video_url); ?>" allowfullscreen></iframe>
            </div>
            <?php endif; ?>
            <p class="mt-4 px-md-5" style="font-size: 1.1rem; color: #555;">
                <?php echo isset($settings['video_description']) && $settings['video_description'] ? nl2br(htmlspecialchars($settings['video_description'])) : "The London Aesthetics Affiliate Program is your chance to earn rewards by helping people discover their best skin. Whether you're a content creator, beauty enthusiast, or skincare professional, our program offers everything you need to succeed."; ?>
            </p>
        </div>
    </div>
</section>

<section class="container text-center my-5">
    <h2 class="section-title">EASY TO START</h2>
    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="start-card">
                <i class="fa-regular fa-pen-to-square"></i>
                <h5>Sign Up For Free</h5>
                <p class="text-muted">Register instantly and get access to your personal affiliate dashboard.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="start-card">
                <i class="fa-solid fa-link"></i>
                <h5>Share Your Unique Link</h5>
                <p class="text-muted">Promote our services on social media, blogs, or with your clients.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="start-card">
                <i class="fa-solid fa-sack-dollar"></i>
                <h5>Earn Commissions</h5>
                <p class="text-muted">Get paid for every successful referral through your link.</p>
            </div>
        </div>
    </div>
    <a href="#forms-section" class="btn btn-main mt-5 px-5" style="text-decoration: none; display: inline-block;">SIGN UP NOW</a>
</section>

<section class="container text-center my-5 py-4">
    <h3 class="fw-bold mb-4"><?php echo isset($settings['who_can_join_title']) && $settings['who_can_join_title'] ? htmlspecialchars($settings['who_can_join_title']) : 'Who Can Join?'; ?></h3>
    <p class="text-muted mb-4 px-md-5"><?php echo isset($settings['who_can_join_desc']) && $settings['who_can_join_desc'] ? nl2br(htmlspecialchars($settings['who_can_join_desc'])) : "This opportunity is open to everyone! Whether you are a <b>beauty influencer</b>, <b>skincare enthusiast</b>, <b>medical professional</b>, or simply want to share your results with friends—you can start earning."; ?></p>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="info-strip">
                <i class="fa-solid fa-circle-info me-2"></i> <?php echo isset($settings['who_can_join_info']) && $settings['who_can_join_info'] ? htmlspecialchars($settings['who_can_join_info']) : 'No prior experience required! We provide you with the tools and support you need.'; ?>
            </div>
        </div>
    </div>
    <a href="#forms-section" class="btn btn-main mt-4 px-5" style="text-decoration: none; display: inline-block;">SIGN UP NOW</a>
</section>

<section class="container my-5" id="forms-section">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('validation_errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $this->session->flashdata('validation_errors'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-md-5">
            <div class="form-box">
                <h4 class="fw-bold mb-4">Login</h4>
                <form method="POST" action="<?php echo base_url('auth/login'); ?>">
                    <input type="hidden" name="return_url" value="<?php echo base_url(); ?>">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <button type="submit" class="btn btn-main w-100">Login</button>
                </form>
                <p class="text-center mt-3 small text-muted">Don't have an account? <a href="<?php echo base_url('auth/signup'); ?>">Register</a></p>
            </div>
        </div>
        <div class="col-md-7">
            <div class="form-box">
                <h4 class="fw-bold mb-4">Register Yourself</h4>
                <form method="POST" action="<?php echo base_url('auth/signup'); ?>" class="row g-2" id="register-form">
                    <input type="hidden" name="return_url" value="<?php echo base_url(); ?>">
                    <?php 
                    $aff_ref = $this->input->get('aff') ?: $this->input->cookie('affiliate_id');
                    if ($aff_ref): ?>
                        <input type="hidden" name="aff" value="<?php echo htmlspecialchars($aff_ref); ?>">
                    <?php endif; ?>
                    <div class="col-md-6"><input type="text" name="full_name" class="form-control" placeholder="Full Name" value="<?php echo set_value('full_name'); ?>" required></div>
                    <div class="col-md-6"><input type="text" name="username" class="form-control" placeholder="Username" value="<?php echo set_value('username'); ?>" required></div>
                    <div class="col-md-12"><input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php echo set_value('email'); ?>" required></div>
                    <div class="col-md-6"><input type="password" name="password" class="form-control" placeholder="Password" required minlength="6"></div>
                    <div class="col-md-6"><input type="password" name="password_confirm" class="form-control" placeholder="Confirm Password" required minlength="6"></div>
                    <?php if (!$aff_ref): ?>
                    <div class="col-md-12"><input type="text" name="aff" class="form-control" placeholder="Referral ID (Optional)" value="<?php echo set_value('aff'); ?>"></div>
                    <?php endif; ?>
                    <div class="col-12"><button type="submit" class="btn btn-main w-100 mt-2">Signup</button></div>
                </form>
                <p class="text-center mt-3 small text-muted">Already have an account? <a href="<?php echo base_url('auth/login'); ?>">Login</a></p>
            </div>
        </div>
    </div>
</section>

<section class="container text-center my-5 pt-5" style="background: #fdf8f4; border-radius: 20px; padding: 40px;">
    <h3 class="fw-bold mb-5"><?php echo isset($settings['feature_section_title']) && $settings['feature_section_title'] ? htmlspecialchars($settings['feature_section_title']) : 'Earn. Grow. Empower. Repeat.'; ?></h3>
    <div class="row">
        <div class="col-md-3">
            <div class="feature-box"><strong>Earn</strong><br><small class="text-muted">Generous Commissions</small></div>
        </div>
        <div class="col-md-3">
            <div class="feature-box"><strong>Grow</strong><br><small class="text-muted">Expand Your Network</small></div>
        </div>
        <div class="col-md-3">
            <div class="feature-box"><strong>Empower</strong><br><small class="text-muted">Help Others Shine</small></div>
        </div>
        <div class="col-md-3">
            <div class="feature-box"><strong>Repeat</strong><br><small class="text-muted">Build Passive Income</small></div>
        </div>
    </div>
    <a href="#forms-section" class="btn btn-main mt-4" style="text-decoration: none; display: inline-block;">JOIN NOW</a>
</section>

<footer class="footer-main">
    <div class="container">
        <div class="row text-center text-md-start">
            <div class="col-md-3 mb-4">
                <img src="<?php echo (isset($settings['site_logo']) && !empty($settings['site_logo']) && $settings['site_logo'] != '#') ? htmlspecialchars($settings['site_logo']) : 'https://affiliates.londonaesthetics.com.pk/wp-content/uploads/2025/06/Logo-scaled-1.png'; ?>" height="40" alt="Logo">
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="footer-title">Working Hours</h6>
                <p class="small text-muted"><?php echo isset($settings['working_hours']) && $settings['working_hours'] ? nl2br(htmlspecialchars($settings['working_hours'])) : 'Mon - Sat: 10am - 8pm<br>Sunday: Closed'; ?></p>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="footer-title">Quick Links</h6>
                <ul class="list-unstyled small text-muted">
                    <li>Affiliate Terms</li>
                    <li>Privacy Policy</li>
                </ul>
            </div>
            <div class="col-md-3 mb-4 text-center">
                <h6 class="footer-title">Let's Get Social</h6>
                <div class="social-icons">
                    <?php if (isset($settings['social_facebook']) && !empty($settings['social_facebook']) && $settings['social_facebook'] != '#'): ?>
                    <a href="<?php echo htmlspecialchars($settings['social_facebook']); ?>" target="_blank"><i class="fa-brands fa-facebook"></i></a>
                    <?php endif; ?>
                    <?php if (isset($settings['social_instagram']) && !empty($settings['social_instagram']) && $settings['social_instagram'] != '#'): ?>
                    <a href="<?php echo htmlspecialchars($settings['social_instagram']); ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if (isset($settings['social_youtube']) && !empty($settings['social_youtube']) && $settings['social_youtube'] != '#'): ?>
                    <a href="<?php echo htmlspecialchars($settings['social_youtube']); ?>" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                    <?php endif; ?>
                    <?php if ((!isset($settings['social_facebook']) || empty($settings['social_facebook']) || $settings['social_facebook'] == '#') && (!isset($settings['social_instagram']) || empty($settings['social_instagram']) || $settings['social_instagram'] == '#') && (!isset($settings['social_youtube']) || empty($settings['social_youtube']) || $settings['social_youtube'] == '#')): ?>
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <hr>
        <p class="text-center small text-muted mt-3"><?php 
            $copyright = isset($settings['footer_copyright']) && $settings['footer_copyright'] ? $settings['footer_copyright'] : '© {year} London Aesthetics UK. All Rights Reserved.';
            echo htmlspecialchars(str_replace('{year}', date('Y'), $copyright)); 
        ?></p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Confirm password validation
    document.getElementById('register-form').addEventListener('submit', function(e) {
        var pwd = this.querySelector('input[name="password"]').value;
        var pwdConfirm = this.querySelector('input[name="password_confirm"]').value;
        if (pwd !== pwdConfirm) {
            e.preventDefault();
            alert('Password and Confirm Password do not match.');
            return false;
        }
    });
</script>
</body>
</html>
