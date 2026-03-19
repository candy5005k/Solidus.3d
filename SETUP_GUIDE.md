# ═══════════════════════════════════════════════════════════════
# AZALEA BACKEND — STEP BY STEP SETUP GUIDE
# For Hostinger Shared Hosting + MySQL
# Written for beginners (vibe coder friendly 😊)
# ═══════════════════════════════════════════════════════════════

## WHAT YOU HAVE AFTER THIS:
## ✅ MySQL database storing every lead
## ✅ Email alert to YOU every time someone fills a form
## ✅ Email confirmation to the user
## ✅ Floor plan images unlock ONLY after form submit
## ✅ Brochure: shows "sent to email" message (no direct download)
## ✅ Same API works for Azalea + Propnmore (just pass site_name)

---

## STEP 1 — Create MySQL Database on Hostinger

1. Login to hPanel (hpanel.hostinger.com)
2. Go to: Hosting → Manage → Databases → MySQL Databases
3. Click "Create Database"
   - Database name: `leads`  → Hostinger auto-prefixes → e.g. `u123456789_leads`
   - Username: `leads`       → auto becomes `u123456789_leads`
   - Password: make a strong one, SAVE IT
4. Click "phpMyAdmin" to open the DB
5. Click the "SQL" tab at the top
6. Open the file `database.sql` from this folder
7. Paste the ENTIRE content → click "Go"
8. You should see: "leads" and "floor_plan_unlocks" tables created ✅

---

## STEP 2 — Edit config.php with YOUR credentials

Open `api/config.php` and change these 4 lines:

```php
define('DB_NAME', 'u123456789_leads');   // ← your actual DB name from hPanel
define('DB_USER', 'u123456789_leads');   // ← your actual DB user from hPanel
define('DB_PASS', 'YourStrongPassword'); // ← the password you set in Step 1
define('NOTIFY_EMAIL', 'your@email.com'); // ← where YOU want lead alerts
```

Also update ALLOWED_ORIGINS — add your actual domains.

---

## STEP 3 — Upload files to Hostinger via File Manager

1. hPanel → File Manager → public_html
2. Inside public_html, create a folder called: `api`
3. Upload these 2 files into `public_html/api/`:
   - config.php
   - leads.php

Your structure should look like:
```
public_html/
├── index.html          ← your Azalea website
├── azalea_rera.png
├── azalea_kalyani_nagar_1__1_.png
├── ... (all your images)
└── api/
    ├── config.php      ← DB + email settings
    └── leads.php       ← the API
```

---

## STEP 4 — Test the API

Open your browser and go to:
`https://yourdomain.com/api/leads.php`

You should see:
```json
{"success":false,"message":"Method not allowed."}
```
That means the API is LIVE ✅ (it only accepts POST, not GET browser visits)

To test POST — open browser console on your site and type:
```js
fetch('/api/leads.php', {
  method: 'POST',
  headers: {'Content-Type':'application/json'},
  body: JSON.stringify({name:'Test',phone:'9999999999',form_type:'enquiry',site_name:'azalea'})
}).then(r=>r.json()).then(console.log)
```
You should get: `{success: true, message: "Thank you!...", token: "..."}`

---

## STEP 5 — Update index.html to call the real API

1. Open `index.html` in VS Code
2. Find the `<script>` tag near the bottom
3. Find these functions and DELETE them (they were dummy):
   - `handleFormSubmit`
   - `handleFpSubmit`
4. Open `frontend_js_snippet.js` from this folder
5. Copy EVERYTHING from it
6. Paste it INSIDE the `<script>` tag, replacing the deleted functions
7. Change line 7 to your actual domain:
   ```js
   const API_URL = 'https://yourdomain.com/api/leads.php';
   ```
8. Save + push to GitHub (or upload via File Manager)

---

## STEP 6 — Test full flow

1. Open your website
2. Fill in an enquiry form → submit
3. Check:
   ✅ Success message shows on screen
   ✅ Floor plans unlock (if floor_plan form)
   ✅ You get an email alert
   ✅ User gets a confirmation email
   ✅ phpMyAdmin → leads table has the new row

---

## STEP 7 — For Propnmore (main site) same API

Just change ONE line in the frontend:
```js
const SITE_NAME = 'propnmore'; // instead of 'azalea'
```
Same `api/leads.php` works. Leads are separated by `site_name` column.

---

## EMAIL NOT ARRIVING? (Common Hostinger issue)

Hostinger's `mail()` function sometimes goes to spam.
Fix: Use Hostinger's Business Email (free with hosting):

1. hPanel → Emails → Create Email Account
   - Create: noreply@yourdomain.com
2. In config.php, set FROM_EMAIL to that exact address
3. For better delivery, use PHPMailer with SMTP:
   - Run in terminal: `composer require phpmailer/phpmailer`
   - Or manually upload phpmailer from github.com/PHPMailer/PHPMailer

---

## VIEW LEADS (Simple way)

phpMyAdmin → your database → leads table → Browse
You'll see every form submission.

---

## FILES SUMMARY

| File                    | Where to upload          | What it does                    |
|-------------------------|--------------------------|---------------------------------|
| database.sql            | Run in phpMyAdmin SQL tab| Creates tables                  |
| api/config.php          | public_html/api/         | DB credentials + settings       |
| api/leads.php           | public_html/api/         | The actual POST + GET API       |
| frontend_js_snippet.js  | Paste into index.html    | Connects forms to API           |

---

## STUCK? Common errors:

❌ "Database connection failed"
→ Wrong DB_NAME / DB_USER / DB_PASS in config.php

❌ "CORS error" in browser console
→ Add your domain to ALLOWED_ORIGINS in config.php

❌ Email not received
→ Check spam folder first. Then see "EMAIL NOT ARRIVING" section above.

❌ 404 on /api/leads.php
→ Files not uploaded to correct folder

That's it! You now have a working backend 🎉
