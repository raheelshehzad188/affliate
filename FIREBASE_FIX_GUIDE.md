# Fix: "Method doesn't allow unregistered callers" Error

यह error fix करने के लिए follow करें:

## Step 1: Enable Identity Toolkit API

1. **Google Cloud Console** खोलें: https://console.cloud.google.com/
2. अपना **Firebase project** select करें (top dropdown से)
3. Left sidebar में **APIs & Services** > **Library** पर click करें
4. Search box में **"Identity Toolkit API"** type करें
5. **Identity Toolkit API** select करें
6. **ENABLE** button click करें

## Step 2: Configure API Key Restrictions

1. **APIs & Services** > **Credentials** पर जाएं
2. अपना **API Key** find करें (जो Firebase config में use हो रहा है)
3. API Key पर **click** करें (edit करने के लिए)
4. **API restrictions** section में:
   - ✅ **Restrict key** select करें
   - **Select APIs** dropdown से:
     - ✅ **Identity Toolkit API** select करें
     - ✅ **Firebase Installations API** (optional, लेकिन recommended)
5. **Application restrictions** section में:
   - **None** select करें (testing के लिए) 
   - या **HTTP referrers** select करें और add करें:
     ```
     http://localhost/*
     http://192.168.*/*
     https://affiliate.londonaestheticsuk.com/*
     ```
6. **SAVE** button click करें

## Step 3: Verify API Key in Config

`application/config/firebase.php` file में check करें:

```php
$config['firebase_api_key'] = 'YOUR_ACTUAL_API_KEY'; // यहाँ actual key होनी चाहिए
```

## Step 4: Test Again

अब OTP send करके test करें:

```bash
POST /user/otp/send
{
  "phone": "+923001234567"
}
```

## Quick Checklist

- [ ] Identity Toolkit API enabled है Google Cloud Console में
- [ ] API Key में Identity Toolkit API restrict किया गया है
- [ ] `firebase.php` में correct API key set है
- [ ] Phone Authentication enabled है Firebase Console में (Authentication > Sign-in method)

## Alternative: Create New API Key (If Still Not Working)

1. Google Cloud Console > APIs & Services > Credentials
2. **+ CREATE CREDENTIALS** > **API key**
3. New API key copy करें
4. **Restrict key** click करें
5. **Identity Toolkit API** enable करें
6. **Save** करें
7. `firebase.php` में new key update करें

## Still Having Issues?

Check करें:
- API key correct है या नहीं
- Project correct select किया है या नहीं
- Billing enabled है या नहीं (कुछ APIs के लिए required होता है)
- Phone number format correct है: `+923001234567` (spaces नहीं)

