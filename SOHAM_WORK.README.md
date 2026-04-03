# Soham Implementation Runbook

Owner: Soham
Project: Tanay Gram Panchayat Portal
Date: 2026-04-03

## Goal
Implement and release these features:
1. SMTP emails for payment confirmation and invoice sending (water + property).
2. Redesigned invoices for water and property tax after payment.
3. Advance due-date reminder emails.
4. Production Firebase OTP (real SMS OTP, not debug fallback).

## Current Known State
- Firebase authorized domains were configured.
- Firebase web app config is available.
- Local DB config was aligned to `gram_panchayat`.
- Laravel non-DB caches were cleared successfully.

## Phase 0 - Environment and Ownership Setup

### Step 0.1 - Gmail and Firebase ownership
- Create and keep ownership under: soham.tanayagency.in@gmail.com.
- Use this same account for:
  - Firebase project
  - SMTP sender identity

### Step 0.2 - Firebase mandatory checks
- Firebase Authentication -> Sign-in method -> Phone: Enabled.
- Firebase Authentication -> Settings -> Authorized domains:
  - neralgov.com
  - www.neralgov.com
  - localhost
  - 127.0.0.1
- Firebase Project Settings -> Web App:
  - Save apiKey
  - Save authDomain
  - Save projectId
  - Save storageBucket
  - Save messagingSenderId
  - Save appId

### Step 0.3 - Laravel runtime checks
Run from project root:

```powershell
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan view:clear
```

Note: avoid `optimize:clear` when DB/cache connection is broken.

## Phase 1 - Fix Firebase OTP (Production)

### Step 1.1 - Update Firebase values in app settings
Update site settings with new Firebase web app values and set `firebase_enabled = 1`.

Optional SQL template:

```sql
USE gram_panchayat;
UPDATE site_settings SET value = 'YOUR_API_KEY' WHERE `key` = 'firebase_api_key';
UPDATE site_settings SET value = 'YOUR_AUTH_DOMAIN' WHERE `key` = 'firebase_auth_domain';
UPDATE site_settings SET value = 'YOUR_PROJECT_ID' WHERE `key` = 'firebase_project_id';
UPDATE site_settings SET value = 'YOUR_STORAGE_BUCKET' WHERE `key` = 'firebase_storage_bucket';
UPDATE site_settings SET value = 'YOUR_SENDER_ID' WHERE `key` = 'firebase_messaging_sender_id';
UPDATE site_settings SET value = 'YOUR_APP_ID' WHERE `key` = 'firebase_app_id';
UPDATE site_settings SET value = '1' WHERE `key` = 'firebase_enabled';
DELETE FROM cache WHERE `key` LIKE 'gram-panchayat-cache-setting_firebase_%';
```

### Step 1.2 - Harden login script behavior
In the citizen login page:
- Keep Firebase initialization strict when `firebase_enabled = 1`.
- Show explicit Firebase errors instead of silent fallback.
- Keep fallback OTP only when Firebase is intentionally disabled.

## Phase 2 - SMTP Setup (Gmail App Password)

### Step 2.1 - Generate Gmail app password
- Enable 2-step verification for soham.tanayagency.in@gmail.com.
- Generate app password for mail sending.

### Step 2.2 - Update mail env values
In `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=tls
MAIL_USERNAME=soham.tanayagency.in@gmail.com
MAIL_PASSWORD=YOUR_GMAIL_APP_PASSWORD
MAIL_FROM_ADDRESS=soham.tanayagency.in@gmail.com
MAIL_FROM_NAME="Neral Gram Panchayat"
```

Then clear config:

```powershell
C:\xampp\php\php.exe artisan config:clear
```

## Phase 3 - Payment Confirmation + Invoice Emails

### Step 3.1 - Add citizen email support
- Add `email` column to citizens table (nullable initially).
- Update model fillable/validation/forms:
  - Admin create/edit citizen forms
  - Citizen profile form

### Step 3.2 - Add email templates and classes
Create mailables:
- Payment success confirmation mail.
- Water invoice mail.
- Property invoice mail.

### Step 3.3 - Trigger only once per successful payment
- Ensure online payment finalization is idempotent.
- Avoid duplicate mail send from callback + redirect race.
- Send mail after successful payment persistence.

## Phase 4 - Advance Due Reminder Emails

### Step 4.1 - Reminder settings
Add configurable settings:
- Reminder enabled
- Reminder lead days (example: 3 days before due)

### Step 4.2 - Scheduler command
Create command to find due bills and send reminders for:
- Water monthly bills
- Property annual bills

### Step 4.3 - Duplicate prevention
Track reminder sent state per bill to prevent repeated daily spamming.

## Phase 5 - Invoice Redesign

### Step 5.1 - Wait for final structures
- Water and property invoice final design will be provided at 12.

### Step 5.2 - Implement templates
- Replace existing invoice blade templates with final designs.
- Keep print-friendly and mobile-friendly layout.
- Include payment details and reference IDs.

## Phase 6 - QA and Release Checklist

### Functional QA
- OTP sends real SMS and verifies correctly.
- No debug OTP appears in production.
- Water tax payment sends confirmation email.
- Property tax payment sends confirmation email.
- Invoice email includes correct bill and amount.
- Reminder email sends before due date with clear warning message.
- No duplicate payment emails for one transaction.

### Data QA
- Site settings store new Firebase values.
- `firebase_enabled` is 1.
- Citizens with email receive mails; missing email is handled gracefully.

### Ops QA
- Queue worker running for queued mails.
- Scheduler running daily.
- Mail logs monitored for failed sends.

---

## Soham Work Checklist

### A. Setup Checklist
- [ ] New Gmail created and secured (2FA enabled).
- [ ] New Firebase project created under Soham account.
- [ ] Phone auth enabled in Firebase.
- [ ] Authorized domains added.
- [ ] Web app created and config copied.
- [ ] Firebase settings updated in admin panel/DB.
- [ ] `firebase_enabled` set to 1.

### B. SMTP Checklist
- [ ] Gmail app password generated.
- [ ] Mail env values updated.
- [ ] Config cache cleared.
- [ ] Test email sent successfully.

### C. Development Checklist
- [ ] Citizens email column migration added.
- [ ] Citizen model and validation updated.
- [ ] Payment confirmation mailable created.
- [ ] Water invoice mailable created.
- [ ] Property invoice mailable created.
- [ ] Idempotent payment completion logic implemented.
- [ ] Reminder command implemented.
- [ ] Reminder scheduler entry added.
- [ ] Reminder duplicate prevention implemented.
- [ ] Invoice templates updated with final design.

### D. Final UAT Checklist
- [ ] Real OTP test passed on neralgov.com.
- [ ] Water payment email + invoice test passed.
- [ ] Property payment email + invoice test passed.
- [ ] Reminder email test passed.
- [ ] No duplicate email/payment rows observed.
- [ ] Deployment sign-off completed.
