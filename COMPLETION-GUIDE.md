# Charity Hub – Setup, Testing & What's Left to Finish

## PART A – Set up on your computer (do in order)
1. **Install XAMPP** (apachefriends.org). Accept defaults.
2. Open **XAMPP Control Panel** → click **Start** on **Apache** and **MySQL** (both turn green).
3. **Unzip** `CHARITY-HUB-complete.zip`. Open the folder until you see `index.php`, `schema.sql`, `auth`, `admin`…
4. **Copy that folder** into `C:\xampp\htdocs\`. Final check: `C:\xampp\htdocs\CHARITY-HUB\index.php` exists.
   (If you still have an older CHARITY-HUB / CHARITY-HUBB folder in htdocs, delete or rename it first.)
5. Open **http://localhost/phpmyadmin** → top **SQL** tab → open `schema.sql` in Notepad → Ctrl+A, Ctrl+C → paste in the box → **Go**.
   You should see database `charity_hub` with 9 tables.
6. Open **http://localhost/CHARITY-HUB/** – you should see the green landing page.
7. Click **Log in**. Use `admin@charityhub.org` / `Password@123`. You land on the Admin Dashboard.
8. Log out and try the other demo accounts (listed on the login page).

## PART B – Test every feature (this is your System / UAT testing evidence)
| # | Test | Expected result |
|---|------|-----------------|
| 1 | Register as Donor | Lands directly on Donor Dashboard (no "confirmation only" page) |
| 2 | Register as Organization | Lands on Org Dashboard with "awaiting verification" notice |
| 3 | Admin → Organizations → Verify | Org projects appear on the landing page and donor pages |
| 4 | Org → Projects → Create | Project shows in the list |
| 5 | Donor → Donate → pick project, KES 1000 | Receipt page with reference number; totals update |
| 6 | Volunteer → Open Events → Sign up | Status Pending |
| 7 | Org → Volunteers → Approve, record hours | Volunteer sees Approved + hours |
| 8 | Beneficiary → Request Assistance | Appears in Org → Beneficiaries |
| 9 | Org → Beneficiaries → Approve with note | Beneficiary sees status + note |
| 10 | Org → Expenses → add | Reports balance = donations − expenses |
| 11 | Admin → Reports → Export CSV / Print | CSV downloads; print dialog opens |
| 12 | Admin → Announcements → Publish | Visible to the chosen audience |
| 13 | Donor opens an admin URL | Redirected back to own dashboard (role-based access) |
| 14 | Wrong password / suspended user | Proper error message |
Screenshot each result for your report.

## PART C – What is built vs your proposal
- User management & registration (5 roles), profile & password change ✔
- Login & authentication: hashed passwords, sessions, CSRF protection, role-based access ✔
- Donation management: donate, receipts, history, per-project progress ✔ (payment is **simulated**)
- Volunteer coordination: events, sign-ups, approval, hours ✔
- Beneficiary tracking: assistance requests and status ✔
- Organization verification, projects, events, expenses ✔
- Communication: announcements per audience ✔
- Reporting & analytics: totals, monthly, by project/method/category, CSV, print/PDF ✔

## PART D – What YOU still need to finish for the project
1. **Run all tests in Part B** and record results (Unit/Integration/System/UAT as in your Chapter 3.6). Ask 3–5 real people to try it (UAT) and note feedback.
2. **Diagrams** (Draw.io): use case, ERD (the 9 tables in schema.sql), class, sequence (donation flow), activity, deployment. The schema is your database schema section.
3. **Screenshots** of every page for the report (admin, org, donor, volunteer, beneficiary, landing).
4. **Real payments (optional, out of scope in proposal):** M-Pesa Daraja STK push would replace the simulated step in `donor/donate.php`.
5. **Email notifications (optional):** PHP `mail()` / PHPMailer.
6. **Before going live:** change all demo passwords, delete demo users, set a MySQL password in `config/db.php`, turn off PHP error display, use HTTPS.
7. **Push to GitHub** (from the project folder, in Command Prompt):
   ```
   git init
   git branch -M main
   git remote add origin https://github.com/mungai19/CHARITY-HUB.git
   git add .
   git commit -m "Complete Charity Hub system"
   git pull origin main --allow-unrelated-histories
   git push -u origin main
   ```
   If the pull shows conflicts, keep your local files (`git checkout --ours .` then commit), or use `git push -u origin main --force` if you want to replace what is on GitHub.

## Troubleshooting
- **Not Found:** folder is not at `C:\xampp\htdocs\CHARITY-HUB`, or URL spelling differs.
- **"Cannot connect to the database":** MySQL not started, schema not imported, or password differs (edit `config/db.php`).
- **Login says incorrect:** import `schema.sql` again (it resets everything). Password is `Password@123`.
- **Page unstyled:** Ctrl+F5. Fonts load from Google Fonts, so unstyled *fonts* offline are normal; layout still works.
- **Apache will not start (port 80 busy):** close Skype/IIS, or change `Listen 80` to `Listen 8080` in httpd.conf and use `http://localhost:8080/CHARITY-HUB/`.
