<?php
// email-template.php
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"> 
  <title>Verify Your Email</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f2f2f2; margin: 0; padding: 0;">
  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center" style="padding: 40px 0;">
        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; padding: 20px; border-radius: 8px;">
          <tr>
            <td style="text-align: center;">
              <h2>Welcome, <?= $user_name ?>!</h2>
              <p>Thanks for signing up. Please verify your email address by clicking the button below.</p>
              <a href="<?= $link; ?>" style="display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Verify Email</a>
              <p style="margin-top: 20px;">If the button doesn't work, copy and paste the following link into your browser:</p>
              <p><a href="<?= $link; ?>" style="color: #2a7ae2;"><?= $link; ?></a></p>
              <p style="margin-top: 40px; font-size: 12px; color: #888;">If you didn’t create an account, you can safely ignore this email.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
