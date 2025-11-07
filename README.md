# Affiliate System - CodeIgniter

WordPress affiliate plugin ko CodeIgniter mein convert kiya gaya hai. Ye complete affiliate management system hai jisme:

## Features

1. **Affiliate Signup** - Naye affiliates signup kar sakte hain
2. **Admin Approval** - Admin affiliates ko approve/reject kar sakta hai
3. **Affiliate Dashboard** - Affiliates apna dashboard dekh sakte hain
4. **Profile Management** - Affiliates apna profile update kar sakte hain
5. **Lead Capture** - Leads capture hote hain aur database mein save hote hain
6. **Commission System** - Multi-level commission structure (Level 1-4)
7. **Admin Panel** - Complete admin panel for managing affiliates, leads, commissions

## Installation

1. **Database Setup:**
   - MySQL database create karein
   - `database_schema.sql` file ko import karein
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123` (Please change this!)

2. **Configuration:**
   - `application/config/database.php` mein database credentials update karein:
   ```php
   'hostname' => 'localhost',
   'username' => 'your_db_username',
   'password' => 'your_db_password',
   'database' => 'your_db_name',
   ```

3. **Base URL:**
   - `application/config/config.php` mein base URL set karein:
   ```php
   $config['base_url'] = 'http://localhost/affliate/';
   ```

4. **Uploads Directory:**
   - `uploads/profile/` directory create ho chuki hai
   - Agar nahi hai to manually create karein aur write permissions den

5. **Email Configuration:**
   - `application/controllers/Auth.php` aur `application/controllers/Lead.php` mein email settings update karein

## Usage

### Affiliate Flow:
1. Affiliate signup karta hai (`/auth/signup`)
2. Email verification link aata hai
3. Email verify karne ke baad, admin approve karta hai
4. Affiliate login karke dashboard use kar sakta hai
5. Affiliate apne links share karta hai
6. Leads capture hote hain
7. Admin leads confirm karta hai
8. Commissions automatically calculate hote hain

### Admin Flow:
1. Admin login karta hai (`/admin/login`)
2. Dashboard se stats dekh sakta hai
3. Affiliates ko approve/reject kar sakta hai
4. Leads ko confirm kar sakta hai (sale amount ke saath)
5. Commissions dekh sakta hai

### Lead Capture:
- Lead form: `/lead/capture` ya `/contact`
- Affiliate link se visit: `/?aff=affiliate_id`
- Lead automatically affiliate se link ho jata hai

## Commission Structure:
- Level 1: 10%
- Level 2: 5%
- Level 3: 2%
- Level 4: 1%

## Files Structure:
```
application/
├── controllers/
│   ├── Admin.php          # Admin panel
│   ├── Affiliate.php      # Affiliate dashboard
│   ├── Auth.php           # Login/Signup
│   ├── Home.php           # Home page
│   └── Lead.php           # Lead capture
├── models/
│   ├── Admin_model.php
│   ├── Affiliate_model.php
│   ├── Click_model.php
│   ├── Commission_model.php
│   └── Lead_model.php
└── views/
    ├── admin/             # Admin views
    ├── affiliate/         # Affiliate views
    ├── auth/              # Login/Signup views
    ├── home/              # Home page
    ├── lead/              # Lead form
    └── layouts/           # Header/Footer
```

## Important Notes:

1. **Security:**
   - Default admin password change karein
   - Email verification enable hai
   - Password hashing use ho rahi hai

2. **HubSpot Integration:**
   - Affiliates apna HubSpot token add kar sakte hain
   - Leads automatically HubSpot mein sync hote hain

3. **Cookies:**
   - Affiliate tracking 30 days ke liye cookie mein store hota hai

4. **File Uploads:**
   - Profile pictures `uploads/profile/` mein save hote hain
   - Directory permissions check karein

## Support

Agar koi issue ho to check karein:
- Database connection
- File permissions
- Base URL configuration
- Email settings

## License

Open source - use as needed.

