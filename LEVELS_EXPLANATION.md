# Commission Levels Explanation

## Levels Kaise Ban Rahe Hain?

### 1. **Referral Chain (referred_by field)**

Jab koi affiliate signup karta hai:
- Agar wo kisi affiliate ke referral link se aata hai → `referred_by` field set hota hai
- Example: Affiliate A ne Affiliate B ko refer kiya → B ka `referred_by = A.id`

### 2. **Commission Levels Structure**

Jab lead confirm hota hai, 4 levels par commission banata hai:

```
Level 1: Jo affiliate ne lead capture kiya (10% commission)
    ↓
Level 2: Us affiliate ka parent (referred_by) → 5% commission
    ↓
Level 3: Parent ka parent → 2% commission
    ↓
Level 4: Parent ka parent ka parent → 1% commission
```

### 3. **Example Scenario**

**Affiliate Chain:**
- Affiliate A (Top level)
- Affiliate B (referred_by = A.id)
- Affiliate C (referred_by = B.id)
- Affiliate D (referred_by = C.id)

**Jab Affiliate D ka lead confirm hota hai:**
- Level 1: Affiliate D → 10% commission
- Level 2: Affiliate C (D ka parent) → 5% commission
- Level 3: Affiliate B (C ka parent) → 2% commission
- Level 4: Affiliate A (B ka parent) → 1% commission

### 4. **Current Implementation**

**Commission Structure (Commission_model.php):**
```php
$commission_structure = [
    ['percent' => 10, 'level' => 1],  // Direct affiliate
    ['percent' => 5,  'level' => 2],  // Parent
    ['percent' => 2,  'level' => 3],  // Grandparent
    ['percent' => 1,  'level' => 4],  // Great-grandparent
];
```

**How it works:**
1. Lead confirm hone par `process_commissions_by_lead()` call hota hai
2. Pehle lead capture karne wale affiliate ko Level 1 commission milta hai
3. Phir `get_parent()` se parent affiliate milta hai
4. Parent ko Level 2 commission milta hai
5. Yeh chain 4 levels tak continue hoti hai

### 5. **Database Structure**

**affiliates table:**
- `id` - Affiliate ID
- `referred_by` - Parent affiliate ID (NULL agar direct signup ho)

**commissions table:**
- `affiliate_id` - Jo affiliate ko commission mil raha hai
- `level` - Commission level (1, 2, 3, ya 4)
- `commission_percent` - Commission percentage
- `commission_amount` - Calculated amount

### 6. **Important Notes**

- Agar affiliate ka koi parent nahi hai (`referred_by = NULL`), to sirf Level 1 commission banega
- Maximum 4 levels tak commission banega
- Har level par commission percentage kam hota jata hai (10% → 5% → 2% → 1%)

