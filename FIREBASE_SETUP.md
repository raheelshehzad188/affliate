# Firebase OTP Setup Guide

## Step 1: Get Firebase API Key

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create a new one)
3. Click on **Project Settings** (gear icon)
4. Go to **General** tab
5. Scroll down to **Your apps** section
6. If you don't have a web app, click **Add app** and select **Web** (</> icon)
7. Copy the **API Key** from the config object

## Step 2: Enable Phone Authentication

1. In Firebase Console, go to **Authentication**
2. Click **Get Started** if not already enabled
3. Go to **Sign-in method** tab
4. Enable **Phone** authentication
5. Add your domain to authorized domains if needed

## Step 3: Configure API Key Restrictions (Important!)

To fix the "unregistered callers" error:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your Firebase project
3. Go to **APIs & Services** > **Credentials**
4. Find your **API Key** (the one from Firebase)
5. Click on the API key to edit it
6. Under **API restrictions**:
   - Select **Restrict key**
   - Add these APIs:
     - **Identity Toolkit API** (required for OTP)
     - **Firebase Installations API** (if needed)
7. Under **Application restrictions**:
   - Select **None** (for testing) OR
   - Select **HTTP referrers** and add your domains:
     - `http://localhost/*`
     - `http://192.168.*/*`
     - `https://affiliate.londonaestheticsuk.com/*`
8. Click **Save**

## Step 4: Update Firebase Config

Edit `application/config/firebase.php` and replace:

```php
$config['firebase_api_key'] = 'YOUR_ACTUAL_API_KEY_HERE';
$config['firebase_auth_domain'] = 'your-project-id.firebaseapp.com';
$config['firebase_project_id'] = 'your-project-id';
$config['firebase_storage_bucket'] = 'your-project-id.appspot.com';
$config['firebase_messaging_sender_id'] = 'YOUR_SENDER_ID';
$config['firebase_app_id'] = 'YOUR_APP_ID';
```

You can find all these values in Firebase Console > Project Settings > General > Your apps > Web app config.

## Step 5: Test OTP Endpoints

### Send OTP:
```bash
POST /user/otp/send
Content-Type: application/json

{
  "phone": "+923001234567"
}
```

### Verify OTP:
```bash
POST /user/otp/verify
Content-Type: application/json

{
  "code": "123456"
}
```

## Troubleshooting

### Error: "Method doesn't allow unregistered callers"
- Make sure **Identity Toolkit API** is enabled in Google Cloud Console
- Check API key restrictions in Google Cloud Console
- Verify API key is correct in `firebase.php`

### Error: "400 Bad Request"
- Check phone number format (must include country code with +)
- Verify Firebase project has Phone Authentication enabled
- Check that API key has proper restrictions set

### Error: "Network error"
- Check if cURL is enabled in PHP
- Verify server can make outbound HTTPS requests
- Check firewall settings

## Frontend Integration Example

```javascript
// Send OTP
async function sendOTP(phone) {
    const response = await fetch('/user/otp/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ phone: phone })
    });
    const data = await response.json();
    return data;
}

// Verify OTP
async function verifyOTP(code) {
    const response = await fetch('/user/otp/verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ code: code })
    });
    const data = await response.json();
    return data;
}
```

