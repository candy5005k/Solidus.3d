# Solidus 3D Modeling - Website Setup Guide

## Project Structure

```text
solidus.3d/
├── index.php            <- Homepage (serves index.html)
├── index.html           <- Landing page (static, 4 251-line polished design)
├── services.php         <- Services page (serves services/index.html)
├── services/index.html  <- Full static services page
├── about.php            <- About page
├── portfolio.php        <- Portfolio / case-study page
├── blog.php             <- Blog listing with category filters
├── blog-post.php        <- Single blog post view
├── contact.php          <- Contact / quote request page
├── login.php            <- Login / Register (AJAX, split-screen)
├── forgot-password.php  <- 3-step password reset (Email → OTP → New Password)
├── dashboard.php        <- User dashboard (admin section for admin role)
├── profile.php          <- Profile view & edit
├── settings.php         <- Change password, notifications, account deletion
├── logout.php           <- Session destroy
├── auth-handler.php     <- Auth API (register, login, profile, password, OTP, delete)
├── why-us.php           <- Why Solidus 3D page
├── process.php          <- Our 5-step process timeline
├── reviews.php          <- Client testimonials
├── faq.php              <- Accordion FAQ
├── privacy.php          <- Privacy policy
├── terms.php            <- Terms & conditions
├── nda.php              <- NDA policy
├── users.php            <- Admin — user management table
├── sitemap.xml          <- XML sitemap
├── robots.txt           <- SEO robots file
├── .htaccess            <- Routing, security headers, caching
├── schema.sql           <- Database schema (users, otp_codes, blog_posts)
├── includes/
│   ├── config.php       <- Site settings, helper functions, admin config
│   ├── db.php           <- PDO connection helpers and blog queries
│   ├── auth.php         <- Session auth helpers (login guards, roles)
│   ├── header.php       <- Shared head, navigation, schema, layout start
│   └── footer.php       <- Shared footer and newsletter CTA
├── config/
│   └── database.php     <- PDO database connection
├── assets/
│   ├── css/main.css     <- Global styles
│   ├── js/main.js       <- Navigation and async form handling
│   ├── images/          <- Logo PNGs, OG image, service images
│   │   └── hero/        <- Hero section images
│   └── uploads/blog/    <- Blog post image uploads
├── api/
│   └── contact.php      <- Contact form backend (CSRF, honeypot, mail)
└── admin/
    └── index.php        <- Blog admin panel (CRUD, image upload)
```

## Quick Start

### 1. Upload to Hosting
Upload the full project to your web root such as `public_html`.

### 2. Create MySQL Database
Suggested values:
- Database: `solidus3d_db`
- User: your hosting MySQL user
- Assign all privileges

### 3. Create Database Tables
Open phpMyAdmin and run the contents of `schema.sql`, which creates:
- `users` — User accounts with bcrypt passwords and roles
- `otp_codes` — 6-digit email verification codes for password reset
- `blog_posts` — Blog content with categories and SEO fields

### 4. Update Config
Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'solidus3d_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_pass');
```

Also update `config/database.php` with the same credentials.

### 5. Set Admin Password Hash
Generate a secure hash once with PHP:

```php
<?php echo password_hash('YourChosenPassword', PASSWORD_DEFAULT); ?>
```

Paste the result into `includes/config.php`:

```php
define('ADMIN_PASS_HASH', 'paste_result_here');
```

### 6. Configure SMTP (Required for Password Reset)
The forgot-password flow sends OTP codes via email. By default `mail()` is used.
For reliable delivery, configure SMTP using PHPMailer or your host's SMTP settings.
If `mail()` fails, OTP codes are logged to `error_log` for development use.

## Authentication System

### User Registration & Login
- Users register and log in via `login.php` (AJAX-powered)
- Passwords are bcrypt-hashed
- Sessions are stored server-side

### Password Reset Flow
1. User visits `forgot-password.php`
2. Enters email → 6-digit OTP is emailed (valid 15 minutes)
3. Enters OTP → Verified against database
4. Sets new password → Redirected to login

### User Dashboard
- Logged-in users see Profile, Settings, Quote, and Services cards
- Admin users also see Blog Admin and User Management cards

### Profile Management
- Users can edit their name and email from `profile.php`
- Users can change password with current-password verification from `settings.php`
- Users can delete their account from the Danger Zone in settings

## Blog Admin

1. Visit `https://your-domain.com/admin/`
2. Log in with `ADMIN_USER` and your chosen password
3. Publish posts with title, content, category, meta fields, and optional image
4. Toggle published state or delete posts from the post list

The panel is responsive and works on phone-sized screens.

## Contact Form

The contact form posts to `api/contact.php` and uses PHP `mail()` by default.
If `mail()` is not configured on the server, the endpoint returns a warning so you know SMTP still needs to be added.

## Clean URLs (via .htaccess)

All pages support clean URLs:
- `/login` → `login.php`
- `/forgot-password` → `forgot-password.php`
- `/dashboard` → `dashboard.php`
- `/profile` → `profile.php`
- `/settings` → `settings.php`
- `/about` → `about.php`
- `/services` → `services/index.html`
- `/portfolio` → `portfolio.php`
- `/blog` → `blog.php`
- `/blog/slug-here` → `blog-post.php?slug=slug-here`
- `/contact` → `contact.php`
- `/why-us`, `/process`, `/reviews`, `/faq`, `/privacy`, `/terms`, `/nda`

## SEO Notes

- Shared meta tags and Open Graph tags are already in `includes/header.php`
- LocalBusiness schema is included in the shared header
- `robots.txt` and `sitemap.xml` are included
- `.htaccess` forces `index.php` ahead of `index.html`
- Security headers: X-Content-Type-Options, X-Frame-Options, Referrer-Policy

## Contact Details

- Email: `info@solidus3dmodeling.com`
- Support: `support@solidus3dmodeling.com`
- Phone: `+91 7420866709`

## Production Checklist

- [ ] Set real database credentials in `includes/config.php` and `config/database.php`
- [ ] Run `schema.sql` in phpMyAdmin to create all tables
- [ ] Set `ADMIN_PASS_HASH` with a bcrypt hash
- [ ] Configure SMTP for reliable email delivery (password reset + contact form)
- [ ] Add real portfolio images/renders
- [ ] Finalize domain in `sitemap.xml` and `robots.txt`
- [ ] Add Google Analytics measurement ID if needed
