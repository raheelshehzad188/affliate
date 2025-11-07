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
<div id="edit_profile" class="sap-section" style="display:none;">
<div class="cover-section"></div>

<div class="edit-profile-container">
    <div class="top-header">
        <h2>Edit Profile</h2>
        <div class="profile-picture-wrapper">
            <img src="<?php echo esc_url($profile_pic_url); ?>" alt="Profile Picture" id="profile-picture">
            <div class="upload-overlay">
                <span class="camera-icon"><i class="fas fa-camera"></i></span>
            </div>
            <input type="file" id="profile-picture-input" style="display:none;">
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="edit-profile-form">
        <?php wp_nonce_field('sap_profile_update', 'sap_profile_update_nonce'); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="sap_name">Name</label>
                <input type="text" name="sap_name" id="sap_name" value="<?php echo esc_attr($user->display_name); ?>" required />
            </div>
            <div class="form-group">
                <label for="sap_email">Email</label>
                <input type="email" name="sap_email" id="sap_email" value="<?php echo esc_attr($user->user_email); ?>" required />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sap_address">Address</label>
                <input type="text" name="sap_address" id="sap_address" value="<?php echo esc_attr(get_user_meta($user->ID, 'address', true)); ?>" />
            </div>
            <div class="form-group">
                <label for="sap_contact">Contact Number</label>
                <input type="text" name="sap_contact" id="sap_contact" value="<?php echo esc_attr(get_user_meta($user->ID, 'contact_number', true)); ?>" />
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sap_city">City</label>
                <input type="text" name="sap_city" id="sap_city" value="<?php echo esc_attr(get_user_meta($user->ID, 'city', true)); ?>" />
            </div>
            <div class="form-group">
                <label for="sap_state">State</label>
                <input type="text" name="sap_state" id="sap_state" value="<?php echo esc_attr(get_user_meta($user->ID, 'state', true)); ?>" />
            </div>
        </div>

        <div class="form-group">
            <label for="sap_bio">Bio</label>
            <textarea name="sap_bio" id="sap_bio" rows="3"><?php echo esc_textarea(get_user_meta($user->ID, 'description', true)); ?></textarea>
        </div>
        <div class="form-group">
            <label for="sap_hubspot_token">HubSpot Access Token (Optional)</label>
            <input type="text" name="sap_hubspot_token" id="sap_hubspot_token" value="<?php echo esc_attr(get_user_meta($user->ID, 'sap_hubspot_token', true)); ?>" />
        </div>

        <div class="form-actions">
            <button type="button" class="cancel-btn" onclick="history.back()">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
        </div>
    </form>
</div>

<script>
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

    jQuery(document).ready(function($) {
        $('.upload-overlay').on('click', function(e) {
            e.preventDefault();
            $('#profile-picture-input').trigger('click');
        });

        $('#profile-picture-input').on('change', function() {
            var file_data = this.files[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('action', 'upload_profile_picture');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                contentType: false,
                processData: false,
                data: form_data,
                success: function(response) {
                    if (response.success) {
                        $('#profile-picture').attr('src', response.data.url);
                    } else {
                        alert('Error uploading image.');
                    }
                }
            });
        });
    });
</script>
</div>