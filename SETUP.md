# Quick Setup Guide

## Step 1: Database Setup

1. **Create Database:**
   ```sql
   CREATE DATABASE affiliate_db;
   ```

2. **Import Schema:**
   ```bash
   mysql -u root -p affiliate_db < database_schema.sql
   ```
   
   Ya phir phpMyAdmin mein:
   - Database `affiliate_db` select karein
   - Import tab kholen
   - `database_schema.sql` file select karein
   - Go click karein

## Step 2: Database Configuration

`application/config/database.php` file mein apne credentials update karein:

```php
'hostname' => 'localhost',
'username' => 'root',        // Apna MySQL username
'password' => '',             // Apna MySQL password (agar hai)
'database' => 'affiliate_db', // Database name
```

## Step 3: Base URL

`application/config/config.php` mein base URL check karein:
```php
$config['base_url'] = 'http://localhost/affliate/';
```

## Step 4: Default Admin Login

After database import, default admin:
- **Username:** `admin`
- **Password:** `admin123`

**⚠️ IMPORTANT:** Login ke baad immediately password change karein!

## Step 5: Test

1. Home: http://localhost/affliate/
2. Admin Login: http://localhost/affliate/admin/login
3. Affiliate Signup: http://localhost/affliate/auth/signup

## Troubleshooting

**"No database selected" error:**
- Database create karein: `affiliate_db`
- Schema import karein
- Database credentials verify karein

**"Headers already sent" error:**
- Usually database connection issue
- Check database credentials
- Make sure database exists

**404 Error:**
- `.htaccess` file check karein
- `mod_rewrite` enable karein Apache mein
- `config.php` mein `index_page` empty hona chahiye

