# Landing Page Polish — "Civic Indigo, Real Essence"

**Date:** 2026-08-11
**Status:** Approved (direction validated visually in brainstorm session; user selected refined Option A)
**Scope:** Public landing page (`home.blade.php`) + shared chrome (header, footer, bottom nav) that is visible on it. Other public pages inherit chrome/token changes by design.

## Goal

Make the landing page feel like a modern Indian government app (RailOne / DigiLocker-grade polish) — especially on mobile — while keeping the site's real identity: navy government theme, trilingual content (en/hi/mr), admin-managed slider and content, and visual consistency with the citizen portal.

## Decisions (from brainstorm)

| Question | Decision |
|---|---|
| Style vibe | Modern gov-app polish — clean, light, navy; NOT flashy dark/marigold |
| Scope | Everything visible on landing, including shared header/footer/bottom-nav; other pages inherit |
| Structure | Keep all sections and content; layout upgrades within them allowed; one additive component (quick-action card) |
| Components/icons | Font Awesome 6 (already loaded) in styled chips — no emoji, no bare icons; refined component treatments throughout |

## Design language

Extend the existing CSS variable system in `public/css/app.css`. No build tooling, no new fonts, no CSS framework.

- **Palette — unify navy with citizen portal** (`resources/views/citizen/layout.blade.php`):
  - `--color-primary: #1e3a5f` (was `#1a365d`)
  - `--color-primary-light: #2d5a8e` (was `#2d5a87`)
  - `--color-primary-dark: #0f1f33` (was `#0f2440`)
  - Note: all public pages shift imperceptibly; this is intentional (one-app feel).
- **New tokens:** `--gradient-hero: linear-gradient(150deg, #0f1f33 0%, #1e3a5f 55%, #2d5a8e 100%)` (matches citizen portal header family); `--gradient-primary: linear-gradient(135deg, #1e3a5f, #2d5a8e)`; blue-tinted soft card shadows (`--shadow-card`, `--shadow-card-lg`); `--radius-2xl: 1.25rem`; frosted-blur surface tokens.
- **Accent discipline:** amber `#d97706` only for: eyebrow kickers, "NEW" badge, bottom-nav active dot. Never large surfaces or primary CTAs.
- **Type:** Inter + Noto Sans Devanagari (unchanged). Hero headline weight 800, `text-wrap: balance` on headings, `font-variant-numeric: tabular-nums` on counters.
- **Icon treatment:** FA icons sit in rounded-square chips (~40px, `--radius-lg`/`--radius-xl`): navy gradient chip (white icon) for primary emphasis, navy tint chip `#eef3f9` (navy icon) for secondary. Grievance keeps red identity as a red tint chip (tokenized, replaces today's inline style).

## Section-by-section

### Header + mobile menu (shared, `layouts/app.blade.php` + CSS)
- Frosted bar: `rgba(255,255,255,.85)` + `backdrop-filter: blur`, hairline bottom border (replaces opaque white + shadow).
- Logo: when no uploaded logo, the `fa-landmark` fallback sits in a small navy-gradient rounded tile. Uploaded logo behavior unchanged.
- Mobile menu panel: rounded bottom corners, larger tap targets (min 44px), staggered item fade-in, login button full-width.
- Desktop links: soft pill hover/active instead of underline.

### Hero slider (`home.blade.php` + CSS)
- Keep slider mechanics, admin images, autoplay, dots. Keep the empty-state slide.
- Base layer: `--gradient-hero` + two soft decorative radial circles (CSS only).
- Photo slides get a real navy scrim (gradient overlay, darker at text side) — today's `.slide-overlay` is transparent and text can be illegible.
- Slide content: eyebrow kicker ("Official Digital Portal" — localized via `__()`), balanced headline, sub, CTA. Refined glass card: lighter blur, thinner border, tighter mobile padding.
- CTA: navy gradient button, white 25% border, arrow chip inside; hover lift.
- Motion: slow Ken Burns zoom on photo slides. Dots: active dot elongates to a pill. Arrows hidden on mobile; the slider JS gains basic touch-swipe (touchstart/touchend delta) so mobile users can swipe between slides — dots remain as fallback.
- Height: ~440px mobile (from 500), 560px desktop (from 600).

### Quick-action card (new component in `home.blade.php`, below hero)
- White elevated card overlapping hero bottom (negative margin), 4 equal actions:
  1. Property Tax → `route('citizen.login')` — `fa-house`
  2. Water Tax → `route('citizen.login')` — `fa-droplet`
  3. Grievance → `route('grievance.create')` — `fa-bullhorn` + amber NEW badge
  4. Certificates → `route('digital-services')` — `fa-file-lines`
- Navy-tint icon chips, labels localized. Desktop: same card, centered `max-width`. Tap: chip scales slightly.

### Stats (`home.blade.php` + CSS)
- Same 4 counters and data-targets. One white card strip, hairline dividers, small FA icon above/beside numeral, navy 800-weight tabular numerals.
- Mobile <480px: 2×2 grid (replaces current 1-col stack). Count-up animation kept.

### Services (`home.blade.php` + CSS)
- Section header: eyebrow label + H2 + "View all →" link to `route('digital-services')`.
- Desktop ≥768px: current auto-fit grid, refined cards — `--radius-2xl`, hairline border, `--shadow-card`, navy gradient icon chip, hover lift + border-tint.
- Mobile <768px: cards become horizontal rows — icon chip left, title + one-line description middle, chevron right; whole row tappable.
- House-tax exclusion and admin-managed `$taxType->icon` respected. Grievance card: red tint chip, same row/card layout.

### Features / "Seamless Experience" (`home.blade.php` + CSS)
- Navy-tint icon chips (not solid navy circles). Mobile: 2-col grid of compact tiles; desktop: 4-col. Text sizes tightened.

### Contact CTA (`home.blade.php` + CSS)
- `--gradient-hero` band with soft decorative circles, white solid button (call) + ghost white button (email). Full-width buttons stacked on <480px (as today).

### Footer (shared, CSS only)
- Background `#0f1f33` (primary-dark) instead of gray-900; headings/links unchanged; social links become rounded-square navy-tint chips; hover → navy gradient.

### Bottom nav (shared, `layouts/app.blade.php` + CSS)
- Frosted blur white bar, `env(safe-area-inset-bottom)` padding (footer/main-content offsets updated accordingly).
- Active item: navy tint pill behind icon + small amber dot under label; springy pill transition. Existing route-active JS logic unchanged.

### Motion (landing-scoped JS + CSS)
- IntersectionObserver adds `.revealed` for gentle fade-up on cards/section headers (stagger via `transition-delay`). Script lives in `home.blade.php` `@push('scripts')`.
- All animation (Ken Burns, reveals, pill transitions) disabled under `@media (prefers-reduced-motion: reduce)`.

## Implementation shape

| File | Work |
|---|---|
| `public/css/app.css` | Bulk: token updates, new tokens, restyled header/hero/quick-actions/stats/services/features/CTA/footer/bottom-nav, motion + reduced-motion rules |
| `resources/views/home.blade.php` | Quick-action card markup, eyebrow kickers, stats icons, services "View all", mobile row markup hooks, reveal classes + observer script |
| `resources/views/layouts/app.blade.php` | Logo emblem fallback markup, minor class hooks for header/bottom nav |

Constraints: vanilla CSS only; FA 6.5.1 already loaded; no removal of admin-managed content paths; all new user-facing strings go through `__('messages.*')` with en/hi/mr entries.

## Testing

- Real browser via local serve: 360, 390, 768, 1200px widths.
- All three locales — Devanagari headlines are longer; check hero, quick actions, nav.
- Slider states: 0 sliders (empty-state), 1 (no nav/dots), many (nav + autoplay).
- Shared-chrome sanity pass on: about, digital-services, grievance form, citizen login, one static/legal page.
- `prefers-reduced-motion` emulation: no Ken Burns, no reveals.
- Bottom nav on a device/emulator with gesture bar (safe-area inset).

## Out of scope

- Dark mode; content/copy changes beyond eyebrows and localized labels; other pages' own content sections; admin panel; design-exploration sandbox pages; performance work beyond not adding new asset requests.
