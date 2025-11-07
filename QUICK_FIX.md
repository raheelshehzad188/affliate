# 🚨 Quick Fix - Database Update Error

## Problem:
"Failed to update affiliate. Please check database connection and column exists."

## Solution:

### Step 1: Run Database Update Script

Browser mein ye URL open karein:
```
http://localhost/affliate/database_update_special_affiliate.php
```

Yeh script `is_special` column add karega.

### Step 2: Verify

Agar script successfully run ho jaye, to ye message dikhega:
- ✅ "Added 'is_special' column to affiliates table"

### Step 3: Test Again

Phir admin panel se affiliate update karein - ab kaam karega!

---

## Alternative: Manual SQL

Agar script kaam na kare, to phpMyAdmin mein ye SQL run karein:

```sql
ALTER TABLE `affiliates` ADD COLUMN `is_special` tinyint(1) DEFAULT 0 AFTER `status`;
```

---

## After Fix:

1. ✅ Admin affiliate update kar sakta hai
2. ✅ Banner image upload ho sakta hai
3. ✅ Special affiliate checkbox kaam karega
4. ✅ Sab fields properly save honge

