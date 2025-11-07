<?php
/* Template Name: Edit Profile */

$affiliate_user_id = isset($_COOKIE['affiliate_user_id']) ? intval($_COOKIE['affiliate_user_id']) : 0;
if (!$affiliate_user_id) {
    wp_redirect(home_url('/affiliate-program'));
                        exit;
}

$user = get_userdata($affiliate_user_id);
$profile_pic_id = get_user_meta($user->ID, 'profile_picture', true);
$profile_pic_url = $profile_pic_id ? wp_get_attachment_url($profile_pic_id) : 'https://via.placeholder.com/150';
?>
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f8f8f8;
        }

        .cover-section {
            background-color: #5C163D;
            height: 180px;
            position: relative;
        }

        .edit-profile-container {
            max-width: 850px;
            margin: -60px auto 0;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            position: relative;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .top-header h2 {
            font-size: 28px;
            color: #5C163D;
            margin: 0;
        }

        .profile-picture-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #C68F4E;
            cursor: pointer;
        }

        .profile-picture-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-picture-wrapper:hover .upload-overlay {
            opacity: 1;
        }

        .camera-icon i {
            font-size: 22px;
            color: white;
        }

        .edit-profile-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
        }

        .save-btn,
        .cancel-btn {
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .save-btn {
            background-color: #5C163D;
            color: white;
        }

        .save-btn:hover {
            background-color: #C68F4E;
            color: white;
        }

        .cancel-btn {
            background-color: #f5f5f5;
            color: #333;
        }

        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
            }

            .top-header {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
        }
    </style>
<div id="change_pass" class="sap-section">
<div class="cover-section"></div>

<?php
echo $message .= '
    <h3>Reset Your Password</h3>
    <form id="sap-password-reset-form">
        <label>Current Password:</label><br>
        <input type="password" name="current_password" id="current_password" required><br><br>

        <label>New Password:</label><br>
        <input type="password" name="new_password" id="new_password" required><br><br>

        <label>Confirm New Password:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required><br><br>

        <button type="submit">Update Password</button>
        <div id="password-reset-response" style="margin-top:10px;"></div>
    </form>

    <script>
    jQuery(document).ready(function($){
        function validatePassword(password) {
            const errors = [];
            if (password.length < 8) errors.push("Minimum 8 characters");
            if (!/[A-Z]/.test(password)) errors.push("At least 1 uppercase letter");
            if (!/[a-z]/.test(password)) errors.push("At least 1 lowercase letter");
            if (!/[0-9]/.test(password)) errors.push("At least 1 number");
            if (!/[\\W]/.test(password)) errors.push("At least 1 special character (e.g. !@#$)");
            return errors;
        }

        $("#sap-password-reset-form").on("submit", function(e){
            e.preventDefault();

            var current_pass = $("#current_password").val();
            var new_pass = $("#new_password").val();
            var confirm_pass = $("#confirm_password").val();
            var errorContainer = $("#password-reset-response");
            errorContainer.html("");

            if (new_pass !== confirm_pass) {
                errorContainer.html("<p style=\'color:red;\'>❌ Passwords do not match.</p>");
                return;
            }

            var validationErrors = validatePassword(new_pass);
            if (validationErrors.length > 0) {
                let errorHtml = "<p style=\'color:red;\'>❌ Weak password. Please include:</p><ul>";
                validationErrors.forEach(err => {
                    errorHtml += "<li style=\'color:gray;\'>• " + err + "</li>";
                });
                errorHtml += "</ul>";
                errorContainer.html(errorHtml);
                return;
            }

            errorContainer.html("Processing...");

            $.ajax({
                url: "'.admin_url('admin-ajax.php').'",
                type: "POST",
                data: {
                    action: "sap_ajax_password_reset",
                    current_password: current_pass,
                    new_password: new_pass,
                    confirm_password: confirm_pass,
                },
                success: function(response){
                    errorContainer.html(response);
                }
            });
        });
    });
    </script>
';
