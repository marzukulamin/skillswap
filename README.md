# SkillSwap

SkillSwap is a PHP/MySQL web application for students to exchange skills, request learning sessions, review completed sessions, and earn badges. It follows the supplied master specification and uses exactly these six database tables:

- `USER`
- `SKILL`
- `SESSION_BOOKING`
- `REVIEW`
- `BADGE`
- `USER_BADGE`

No additional database entity or relationship has been introduced.

## Technology

- HTML5 and Bootstrap 5
- JavaScript
- PHP 8+
- MySQL 8+ / MariaDB as supplied with a current XAMPP release
- XAMPP Apache and phpMyAdmin for local development

## Current functionality

- Secure phone-and-password registration and login
- Student/admin role and account-status checks
- Student profile and safe profile-image upload
- Skill create, read, update, delete, visibility, search, category, and level filters
- Session requests, learner/mentor views, scheduling, status, meeting link, and payment status
- One review per completed booking
- Badge definitions and assignments
- Admin management for users, skills, bookings, reviews, and badges
- Live aggregate reports for popular skills, active mentors, ratings, booking statuses, categories, and new users
- Prepared SQL statements, CSRF protection, output escaping, ownership checks, and password hashing

## Local setup with XAMPP

1. Install and open XAMPP, then start Apache and MySQL.
2. Copy this project folder to `<XAMPP_INSTALLATION>\htdocs\skillswap`.
3. Open `http://localhost/phpmyadmin/`.
4. Use the **Import** tab to run `sql/skillswap.sql`.
5. Optionally import `sql/seed_badges.sql` for four starter badge definitions.
6. Open `http://localhost/skillswap/` and register a user.

The default database connection is `localhost`, user `root`, blank password, database `skillswap`, port `3306`. If your configuration differs, copy `includes/config.local.example.php` to `includes/config.local.php` and edit only the local copy.

## Create the first administrator

Registration intentionally creates student accounts only. After registering the intended administrator, run this in phpMyAdmin, replacing the phone number:

```sql
UPDATE `USER`
SET role = 'admin'
WHERE phone = '01700000000';
```

Sign out and sign back in to activate the admin navigation.

## Application status values

The schema stores statuses as `VARCHAR`, so the application consistently uses:

- User status: `active`, `inactive`, `suspended`
- Skill status: `active`, `inactive`
- Session status: `pending`, `accepted`, `rejected`, `completed`, `cancelled`
- Payment status: `unpaid`, `paid`, `waived`

These values do not alter the locked schema.

## Suggested test sequence

### Guided client demo

Open `demo.php` or choose **Try guided demo** in the navigation. Visitors can test Learner, Mentor, and Administrator demo accounts without credentials. The guide follows one sample booking through creation, the mentor inbox, acceptance, completion, review, admin reporting, and badge assignment. Each action updates all three demo views. Steps advance only after successful actions; visitors can switch roles freely and restart at any time.

This is a session-backed product sandbox, separate from authenticated database workflows. It uses no additional tables and never writes to live accounts or bookings. Each browser session has private sample data; closing/expiring the session clears it. Demo roles do not grant access to real admin pages. The standalone demo page works without MySQL; live pages still require the normal database setup.


1. Register two student accounts and one administrator account.
2. Add a skill from the first student.
3. Sign in as the second student and request that skill.
4. Sign in as the skill owner, accept the request, add a meeting link, and mark it completed.
5. Sign in as the learner and submit a review.
6. Sign in as admin, assign a badge, and inspect the dashboard reports.

## Known schema limitations

The locked schema has no persistent notifications, credit ledger, normalized category table, login history, session duration, or separate online/offline session-mode field. Those features have not been simulated with hidden tables or columns.
