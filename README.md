# Charity Hub – Centralized Web-based Charity Platform
PHP 8 + MySQL + HTML/CSS/JS. Roles: Admin, Organization, Donor, Volunteer, Beneficiary.

## Quick setup (XAMPP, Windows)
1. Install XAMPP, start **Apache** and **MySQL** in the Control Panel.
2. Copy this folder (`CHARITY-HUB`) into `C:\xampp\htdocs\` so that `C:\xampp\htdocs\CHARITY-HUB\index.php` exists.
3. Open http://localhost/phpmyadmin → **SQL** tab → paste the whole of `schema.sql` → **Go**.
4. Open http://localhost/CHARITY-HUB/ (landing page) and click **Log in**.

## Demo accounts (password for all: `Password@123`)
| Role | Email |
|---|---|
| Admin | admin@charityhub.org |
| Organization (verified) | org@charityhub.org |
| Organization (pending) | org2@charityhub.org |
| Donor | donor@charityhub.org |
| Volunteer | volunteer@charityhub.org |
| Beneficiary | beneficiary@charityhub.org |

## Database password?
Edit `config/db.php` if your MySQL root user has a password.

## Folder map
- `index.php` landing page · `auth/` login, register, logout · `profile.php`, `announcements.php`
- `admin/`, `organization/`, `donor/`, `volunteer/`, `beneficiary/` – one folder per role
- `includes/` – bootstrap (session, DB helpers, CSRF), layout header/footer, shared report page
- `assets/css/` – `app.css` (design system), `landing.css`, `auth.css` · `assets/js/app.js`
- `schema.sql` – tables + demo data
