# ✅ Final Setup Checklist - Affiliate System

## 🗄️ Database Setup (IMPORTANT - Run First!)

### Step 1: Add `is_special` Column
**URL:** `http://localhost/affliate/database_update_special_affiliate.php`

Yeh script run karein taake `is_special` column add ho jaye.

### Step 2: Verify Database
Check karein ke ye columns exist karte hain:
- ✅ `is_special` (tinyint, default 0)
- ✅ `slug` (varchar, unique)
- ✅ `profile_picture` (varchar)
- ✅ `cover_image` (varchar)

---

## 👨‍💼 Admin Features (Full Access)

### Admin Can Update:
1. ✅ **Full Name** - Update kar sakta hai
2. ✅ **Status** - Pending/Active/Inactive
3. ✅ **Website** - Update kar sakta hai
4. ✅ **Slug** - Unique slug set kar sakta hai
5. ✅ **Password** - Affiliate ka password change kar sakta hai
6. ✅ **Profile Picture** - Upload/Update kar sakta hai
7. ✅ **Banner Image** - Hamesha upload/update kar sakta hai (checkbox se independent)
8. ✅ **Special Affiliate** - Checkbox se permission de sakta hai

### Admin Pages:
- ✅ Dashboard: `admin/dashboard`
- ✅ Affiliates List: `admin/affiliates`
- ✅ Affiliate Detail: `admin/affiliate_detail/{id}`
- ✅ Leads: `admin/leads`
- ✅ Commissions: `admin/commissions`
- ✅ Change Password: `admin/change_password`

---

## 👤 Affiliate Features (Based on Permissions)

### Affiliate Can Update:
1. ✅ **Full Name** - Hamesha
2. ✅ **Website** - Hamesha
3. ✅ **Bio** - Hamesha
4. ✅ **HubSpot Token** - Hamesha
5. ✅ **Profile Picture** - Hamesha
6. ✅ **Banner Image** - Sirf agar `is_special = 1` ho

### Affiliate Pages:
- ✅ Dashboard: `affiliate/dashboard` (with graph)
- ✅ Commissions: `affiliate/commissions`
- ✅ Links: `affiliate/links`
- ✅ Profile: `affiliate/profile`
- ✅ Change Password: `affiliate/change_password`
- ✅ Landing Page: `domain.com/{slug}`

---

## 🔐 Authentication

### Password System:
- ✅ **MD5 Hashing** - Sab passwords MD5 se hash hote hain
- ✅ Admin password change kar sakta hai
- ✅ Affiliate password change kar sakta hai
- ✅ Admin affiliate ka password change kar sakta hai

### Default Admin:
- **Username:** `admin`
- **Password:** `admin123`
- **Email:** `admin@example.com`

---

## 💰 Commission System

### Multi-Level Commissions:
- ✅ **Level 1:** 10% (Direct affiliate)
- ✅ **Level 2:** 5% (Parent)
- ✅ **Level 3:** 2% (Grandparent)
- ✅ **Level 4:** 1% (Great-grandparent)

### Commission Status:
- ✅ **Pending** - Lead pending
- ✅ **Confirmed** - Lead confirmed (automatic)
- ✅ **Paid** - Commission paid
- ✅ **Cancelled** - Commission cancelled

### Dynamic Status:
- Jab admin lead confirm karta hai → Commission status automatically "confirmed" ho jata hai

---

## 📊 Dashboard Features

### Affiliate Dashboard:
- ✅ Stats cards (Clicks, Leads, Commissions)
- ✅ **Weekly Performance Graph** - Google Charts
- ✅ Recent commissions list

### Admin Dashboard:
- ✅ Total affiliates count
- ✅ Total leads count
- ✅ Total commissions
- ✅ Date filters

---

## 🔗 Landing Pages

### Unique Landing Pages:
- ✅ Har affiliate ka apna landing page: `domain.com/{slug}`
- ✅ Slug signup time automatically generate hota hai
- ✅ Admin slug change kar sakta hai
- ✅ Lead capture form landing page par
- ✅ Click tracking automatic

---

## 🎨 Image Uploads

### Profile Picture:
- ✅ Max size: 2MB
- ✅ Formats: gif, jpg, jpeg, png
- ✅ Auto-encrypted filenames
- ✅ Admin aur Affiliate dono upload kar sakte hain

### Banner Image:
- ✅ Max size: 3MB
- ✅ Formats: gif, jpg, jpeg, png
- ✅ Admin hamesha upload kar sakta hai
- ✅ Affiliate sirf special ho to upload kar sakta hai

---

## 🔍 Filters & Search

### Admin Leads Filter:
- ✅ By Affiliate
- ✅ By Status (Pending/Confirmed)
- ✅ By Date Range (From/To)
- ✅ Pagination with filters preserved

### Admin Commissions Filter:
- ✅ By Affiliate
- ✅ By Status (Pending/Confirmed/Paid)
- ✅ By Date Range

---

## ⚙️ Special Affiliate Feature

### How It Works:
1. Admin affiliate detail page par "Special Affiliate" checkbox check kare
2. Agar checked ho → Affiliate apne profile se banner change kar sakta hai
3. Agar unchecked ho → Affiliate ko banner field show nahi hogi
4. **Admin hamesha banner change kar sakta hai** (checkbox se independent)

---

## 🚨 Common Issues & Solutions

### Issue: "Failed to update affiliate"
**Solution:**
1. Database update script run karein: `database_update_special_affiliate.php`
2. Check karein ke `is_special` column exists karta hai
3. Check database connection

### Issue: Banner field show nahi ho rahi
**Solution:**
1. Checkbox check karein (admin page par)
2. Affiliate profile page refresh karein
3. Check `is_special` column value (should be 1)

### Issue: Graph show nahi ho raha
**Solution:**
1. Internet connection check karein (Google Charts CDN)
2. Browser console check karein for errors
3. Check ke `graph_data` array properly pass ho raha hai

---

## 📝 Database Update Scripts

### Required Scripts (Run in Order):
1. ✅ `database_update_special_affiliate.php` - Add is_special column
2. ✅ `database_update_commission_status.php` - Add confirmed status
3. ✅ `database_update_slug.php` - Add slug column (if needed)

### Fresh Database:
- ✅ `database_fresh.php` - Drop and recreate database
- ✅ `database_setup.php` - Initial setup

---

## ✅ Final Verification

### Check These URLs:
1. ✅ `http://localhost/affliate/admin/login` - Admin login
2. ✅ `http://localhost/affliate/auth/signup` - Affiliate signup
3. ✅ `http://localhost/affliate/admin/affiliates` - Affiliates list
4. ✅ `http://localhost/affliate/admin/affiliate_detail/2` - Affiliate detail
5. ✅ `http://localhost/affliate/affiliate/dashboard` - Affiliate dashboard
6. ✅ `http://localhost/affliate/{slug}` - Landing page

### Test These Features:
1. ✅ Admin affiliate update (all fields)
2. ✅ Admin banner image upload
3. ✅ Affiliate profile update
4. ✅ Special affiliate checkbox
5. ✅ Commission status change
6. ✅ Lead confirmation
7. ✅ Graph display

---

## 🎯 Summary

**Sab kuch set hai!** Bas ye karein:

1. **Database Update:** `database_update_special_affiliate.php` run karein
2. **Test:** Admin panel se affiliate update karein
3. **Verify:** Affiliate profile page check karein

Agar koi issue ho to error logs check karein ya database connection verify karein.

