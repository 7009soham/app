# Soham Implementation Runbook

Owner: Soham
Project: Tanay Gram Panchayat Portal
Date: 2026-04-03
Last Updated: 2026-04-16

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

## Recent Fixes (from this chat)

### 1) Dashboard “Transactions” count only after PhonePe API-confirmed success

Problem:
- Dashboard transaction/payment counts were getting inflated when users cancelled payment, pressed back, or payment status was not actually confirmed by PhonePe.

Fix approach:
- Make “completed payment” strict and consistent everywhere.
- Mark success ONLY when confirmed via PhonePe Status API.

Implementation (key changes):
- Strict completed criteria:
  - `app/Models/TaxPayment.php` → `scopeCompleted()` now requires:
    - `status = success`
    - `payment_status = completed`
    - `paid_at IS NOT NULL`
- PhonePe status verification gating:
  - `app/Http/Controllers/Citizen/PaymentController.php`
    - Callback path confirms via `PhonePeService::checkPaymentStatus()` before finalizing success.
    - Added no-downgrade behavior (don’t flip a completed success back to failed due to delayed callbacks).
    - Sets `paid_at` only for confirmed success.
- More failure state coverage:
  - `app/Services/PhonePeService.php` treats `CANCELLED` / `PAYMENT_ERROR` as failed.
- Dashboard counts aligned:
  - `app/Http/Controllers/Admin/DashboardController.php` uses `completed()` scope for counts.
  - `app/Http/Controllers/Citizen/DashboardController.php` and `resources/views/citizen/dashboard.blade.php` show only confirmed successful count.

Quick verification:
- Try payment → cancel/back → dashboard count should NOT increase.
- Complete payment successfully → after redirect/status confirmation the count should increase.

### 2) Errors / confusion when adding Water Tax and Property Tax records

Observed issues:
- Demand filtering on index pages was using old `demand_number` values (1..8) but controllers were filtering using `demand_id` (FK to `demands`).
- Optional numeric fields could become `NULL` on submit (because Laravel converts empty strings to null), which can break inserts/updates or cause warnings in views.

Fix approach:
- Make Demand filtering consistent with the new schema (`demands` + `demand_id`).
- Normalize optional numeric fields to 0 before saving.

Implementation:
- Demand filters fixed in index pages:
  - `resources/views/admin/water-tax/index.blade.php` → filter uses `demand_id` and lists real Demands.
  - `resources/views/admin/property-tax/index.blade.php` → filter uses `demand_id` and lists real Demands.
- Normalize numeric inputs before DB write:
  - `app/Http/Controllers/Admin/WaterTaxController.php` → `amount_paid` defaults to `0`.
  - `app/Http/Controllers/Admin/PropertyTaxController.php` → `previous_*` and `previous_total` default to `0`.
- View safety:
  - `resources/views/admin/water-tax/index.blade.php` uses `number_format($record->amount_paid ?? 0)`.

Quick verification:
- Add a record from Admin → it should save even if optional numeric fields are left blank.
- Filter by Demand on index → should show correct records.

### 3) Citizen Email Linking Confirmation (new mail)

What was added:
- When a Citizen email is linked for the first time (created with email OR updated from empty → set), send a confirmation email.

Implementation:
- `app/Models/Citizen.php` model events (`created` / `updated`) trigger the email only on first-link.
- New mailable: `app/Mail/CitizenEmailLinkedMail.php`
- New template: `resources/views/emails/citizens/email-linked.blade.php`

Quick verification:
- Update a Citizen that previously had no email → set email → one confirmation email should be sent.
- Updating email from one non-empty email to another should NOT re-trigger the “linked” mail.

### 4) Admin-side System Email Simulation (full notification flow)

What was added:
- A reusable simulation service that generates and sends all key citizen-facing system emails in one run.
- An Admin Settings panel action to trigger simulation for any recipient email.
- An Artisan command for QA/testing from terminal.
- New professional templates for invoice generation, payment failure updates, and completion/status notifications.

Implementation:
- Service: `app/Services/SystemEmailSimulationService.php`
  - Generates realistic payloads for:
    - Invoice generation
    - Due date reminder
    - Payment success update
    - Payment failure update
    - Completion/status notification
  - Writes HTML previews to `storage/app/public/mail-previews/<run_id>/`.
- Admin trigger:
  - Controller action in `app/Http/Controllers/Admin/SettingsController.php`
  - Route in `routes/web.php`: `admin.settings.simulate-emails`
  - UI panel in `resources/views/admin/settings/index.blade.php`
- CLI trigger:
  - `app/Console/Commands/SimulateSystemEmails.php`
  - Command: `php artisan notifications:simulate-system-emails --to=soham.tare@somaiya.edu --name="Soham Tare"`
- New mailables/templates:
  - `app/Mail/InvoiceGeneratedMail.php` + `resources/views/emails/payments/invoice-generated.blade.php`
  - `app/Mail/PaymentStatusUpdateMail.php` + `resources/views/emails/payments/status-update.blade.php`
  - `app/Mail/CompletionStatusNotificationMail.php` + `resources/views/emails/notifications/completion-status.blade.php`

Verification executed:
- Date: 2026-04-05
- Recipient: soham.tare@somaiya.edu
- Result: 5 emails sent, 0 failed
- Preview output folder:
  - `storage/app/public/mail-previews/20260405_100022_l2illg/`
  - `01-invoice-generation.html`
  - `02-due-date-reminder.html`
  - `03-payment-status-success.html`
  - `04-payment-status-failure.html`
  - `05-completion-status-notification.html`

  ### 5) Property Tax: removed customer number and phone usage in module UI

  What was changed:
  - Removed customer number and phone fields from Property Tax create/edit forms.
  - Removed phone from Property Tax list/detail views and export CSV headers/rows.
  - Removed customer number visibility from citizen-facing property tax and property invoice screens.

  Implementation details:
  - `app/Http/Controllers/Admin/PropertyTaxController.php`
    - Search now uses property number/name/aadhaar (no customer_no/phone).
    - `store()` / `update()` no longer validate `customer_no` and `phone` from request.
    - For DB compatibility, `customer_no` is auto-filled from `property_no` during save/update.
    - Export and bulk export no longer include phone.
  - `resources/views/admin/property-tax/*.blade.php`
    - Create/edit/index/show updated to remove customer_no and phone inputs/displays.
  - `resources/views/citizen/property-tax.blade.php`
    - Customer number removed; property number shown where relevant.
  - `resources/views/citizen/pay-bill.blade.php`
    - Property flow shows property number, not customer number.
  - `resources/views/citizen/billing/property-invoice.blade.php`
    - Customer number card removed.

  Quick verification:
  - Admin Property Tax create/edit pages show no customer number and no phone fields.
  - Admin Property Tax list/search/export has no phone column.
  - Citizen Property Tax and Property Invoice screens do not show customer number.

  ### 6) Citizen profile email flow hardened against OTP bypass

  Problem fixed:
  - Users could type directly in profile email field and save without completing OTP.

  Final behavior implemented:
  - Add/change email: requires OTP verification before DB update.
  - Remove email: direct removal is allowed without OTP (as requested later).

  Implementation details:
  - `app/Http/Controllers/Citizen/DashboardController.php`
    - Profile update now updates name/address directly.
    - For email add/change, creates a pending OTP request session and does not write email until verification.
    - For email removal, clears email + verification/OTP fields directly.
  - `app/Http/Controllers/Citizen/AuthController.php`
    - Email OTP APIs now apply only set/change flow.
    - Verification applies pending set/change request only after OTP success.
  - `resources/views/citizen/profile.blade.php`
    - Remove Email button shown only when an email exists.
    - OTP modal/messages aligned to set/change flow only.
    - Reset logic keeps remove flag state clean.

  Quick verification:
  - Changing email from profile does not persist without OTP verification.
  - Removing email from profile removes immediately and clears verification state.

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
- [x] Admin-side system email simulation endpoint added.
- [x] Simulation Artisan command added.
- [x] Professional simulation templates for invoice generation, payment failure, and completion/status added.

### D. Final UAT Checklist
- [ ] Real OTP test passed on neralgov.com.
- [ ] Water payment email + invoice test passed.
- [ ] Property payment email + invoice test passed.
- [ ] Reminder email test passed.
- [ ] No duplicate email/payment rows observed.
- [x] Full system email simulation run for Soham completed (5 sent, 0 failed).
- [ ] Deployment sign-off completed.

### E. Bugfixes (Already Applied in Code)
- [x] Dashboard counts only confirmed successful payments (no inflated counts on cancel/back).
- [x] PhonePe callback/redirect only finalizes success after Status API confirmation.
- [x] Prevents downgrading already-completed payments due to delayed callbacks.
- [x] Admin Water/Property Demand filters use `demand_id` + Demands list (not old `demand_number`).
- [x] Admin Payments Panel + Tax Collection Demand filters use `demand_id` + Demands list.
- [x] Tax Rate Adjustment uses `demand_id` + Demands list (no `demand_number` SQL errors).
- [x] Normalizes optional numeric fields to `0` to avoid inserting NULL into NOT NULL decimals.
- [x] Citizen phone is optional end-to-end (validation + DB nullable column).
- [x] Added missing admin show views for Water Tax and Property Tax records.
- [x] Analytics Demand-wise table uses the real Demands list (not hardcoded 1..8).
- [x] Pagination keeps active filters on key admin lists (`withQueryString()`).
- [x] Property Tax module no longer uses customer number and phone in forms/lists/show/export.
- [x] Citizen Property Tax and Property Invoice no longer display property customer number.
- [x] Citizen profile email add/change cannot bypass OTP via direct form typing.
- [x] Citizen profile email remove works directly without OTP (requested behavior).
