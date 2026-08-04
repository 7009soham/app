# PWA and Play Store packaging

The site is an installable PWA. This document covers what is already in place
and the remaining steps to publish it to Google Play as a Trusted Web Activity
(TWA).

## What is in the repo

| File | Purpose |
|------|---------|
| `public/manifest.json` | App name, icons, colours, shortcuts |
| `public/sw.js` | Service worker (see caching rules below) |
| `public/offline.html` | Shown when a navigation fails offline |
| `public/icons/*` | Generated icon set |
| `scripts/generate-pwa-icons.php` | Regenerates the icon set |

Icons are placeholders drawn from the site's navy (`#1a365d`). Replace the
files in `public/icons/` with the official emblem when one exists — no code
references the artwork, only the filenames. Re-run the generator, or drop in
your own PNGs at the same sizes.

## Caching rules (read before changing sw.js)

This portal shows outstanding tax and takes payments, so the service worker is
deliberately restrictive:

- **HTML is never cached.** A cached page would mean a stale balance, an
  expired CSRF token (every form then fails with a 419), or — on a shared
  phone — one citizen's authenticated page shown to the next person.
- **Only same-origin static assets are cached** (`/css`, `/js`, `/icons`,
  `/build`, `/images`, `/fonts`, and asset file extensions).
- **`/admin`, `/citizen`, `/payment`, `/grievance/submit` and `/language` are
  never cached** at all.
- **Non-GET requests bypass the worker entirely**, so payments, logins and
  grievance submissions always hit the network.

`tests/Feature/PwaTest.php` guards these rules. If you loosen them, that test
should fail — treat it as a prompt to think, not an obstacle to delete.

Bump `VERSION` in `sw.js` when static assets change; the activate handler
deletes caches from older versions.

## Packaging for Play

Use [PWABuilder](https://www.pwabuilder.com/) — it wraps Google's Bubblewrap
and handles the Android signing key.

1. Enter `https://www.neralgov.com` and let it analyse the manifest.
2. Package for Android. Keep **"Signing key: create new"** and download the zip.
3. The zip contains the `.aab` for Play and an `assetlinks.json`.
4. Upload `assetlinks.json` to the server so it is reachable at
   `https://www.neralgov.com/.well-known/assetlinks.json`:

   ```bash
   mkdir -p /home/neralgov/htdocs/www.neralgov.com/public/.well-known
   # copy assetlinks.json into that directory, then:
   curl -sS https://www.neralgov.com/.well-known/assetlinks.json
   ```

   Without this file the app opens with a browser URL bar visible, which
   usually fails review. It must be served as `application/json` over HTTPS
   with no redirect.

5. **Keep the signing key safe.** Losing it means you can never update the app
   under the same listing.

## Play Console requirements

- **Account ownership.** Play policy requires an app representing a government
  entity to be published by that entity. Publishing "Neral Gram Panchayat" from
  an individual account risks rejection under the impersonation policy — get
  written authorisation from the Gram Panchayat before submitting, and prefer
  an organisation account.
- **Individual accounts publish the developer's real name and physical address**
  on the public listing.
- **Closed testing.** Individual accounts created after 13 Nov 2023 need 12
  testers opted in for 14 continuous days before production release.
- **Privacy policy URL:** `https://www.neralgov.com/privacy-policy`
- **Data safety form.** Declare what is actually collected: name, phone number,
  address, **Aadhaar number**, property records and payment data. The
  declaration must match reality.
- **Play Billing is not required.** Taxes and government fees are real-world
  services and are exempt, so PhonePe/Razorpay stay as they are and Google
  takes no commission.
- **Minimum functionality.** Plain webview wrappers get rejected. A TWA with a
  valid manifest, service worker and offline handling — which this is — meets
  the bar.

## Firebase OTP inside the TWA

Citizen login uses Firebase phone auth. A TWA runs in Chrome, so the flow works,
but the domain must be authorised in the Firebase console for the active
project:

**Authentication → Settings → Authorized domains →** add `www.neralgov.com`.

Phone sign-in must also be enabled under **Authentication → Sign-in method**.

## Verifying installability

In Chrome DevTools on the live site: **Application → Manifest** (no errors, icons
listed) and **Application → Service workers** (activated and running). Lighthouse's
PWA audit should report the app as installable.
