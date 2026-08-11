# Landing Page "Civic Indigo" Polish — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle the public landing page (and the shared header/footer/bottom-nav chrome) into a modern Indian gov-app look — navy unified with the citizen portal, quick-action card, refined components — per the approved spec `docs/superpowers/specs/2026-08-11-landing-page-polish-design.md`.

**Architecture:** Pure presentation work in three surfaces: `public/css/app.css` (token system + component styles), `resources/views/home.blade.php` (landing markup + landing-only JS), `resources/views/layouts/app.blade.php` (shared chrome markup). Blade markup changes are TDD'd via PHPUnit feature tests (sqlite in-memory, `RefreshDatabase`); CSS-only changes are verified by keeping the suite green plus a final real-browser pass.

**Tech Stack:** Laravel Blade, vanilla CSS (CSS variables, no build step), Font Awesome 6.5.1 (already loaded), Inter + Noto Sans Devanagari (already loaded), PHPUnit.

## Global Constraints

- Navy trio everywhere: `#1e3a5f` (primary), `#2d5a8e` (primary-light), `#0f1f33` (primary-dark) — the citizen-portal values. No `#1a365d` family left in touched files.
- Amber `#d97706` (`--color-accent`) appears ONLY as: slide kicker color, `NEW` badge, bottom-nav active dot.
- Icons are Font Awesome 6 classes inside styled chips — never bare `<i>` sitting on the background, never emoji.
- All new user-visible strings go through `__('messages.<key>')` and get en + hi + mr entries in `resources/lang/{en,hi,mr}/messages.php`.
- Every animation added must be disabled under `@media (prefers-reduced-motion: reduce)`.
- No new asset requests (no new fonts, libraries, or images). No build tooling.
- Admin-managed content paths must keep working: slider rows (`$sliders`), tax types (`$taxTypes`, incl. `->icon` from DB and the house-tax `@continue`), settings, quick links.
- Tests: `php artisan test --filter=<Name>`. Commit after every task; commit messages must not contain double-quote characters (PowerShell 5.1 mangles them).

## File Structure

| File | Responsibility |
|---|---|
| `public/css/app.css` | All styling: tokens (`:root`), chrome, landing sections, shared `.icon-chip` component, motion |
| `resources/views/home.blade.php` | Landing markup: hero (kicker/`slide-bg`/CTA), quick-action card, stats icons, services/features restructure, reveal hooks, slider swipe + reveal JS |
| `resources/views/layouts/app.blade.php` | Shared chrome markup: logo emblem fallback, `theme-color` meta |
| `resources/lang/{en,hi,mr}/messages.php` | 5 new keys: `official_digital_portal`, `quick_actions`, `grievance`, `certificates`, `new` |
| `public/manifest.json` | `theme_color` update |
| `public/sw.js` | Cache `VERSION` bump (last task) |
| `tests/Feature/HomePageEnhancementsTest.php` | One test class, grown task by task |

Shared CSS component contract (defined Task 4, consumed by Tasks 5–7):
`.icon-chip` (44px rounded square, flex-centered) with modifiers `.icon-chip--tint` (navy on `--tint-primary`), `.icon-chip--gradient` (white on `--gradient-primary`), `.icon-chip--danger` (red `#dc2626` on `#fdecec`).

---

### Task 1: Token unification (navy + new tokens, meta/manifest)

**Files:**
- Modify: `public/css/app.css:7-77` (`:root`), plus 4 hardcoded `rgba(26, 54, 93, …)` occurrences
- Modify: `resources/views/layouts/app.blade.php:12` (`theme-color`)
- Modify: `public/manifest.json:9` (`theme_color`)

**Interfaces:**
- Consumes: nothing
- Produces: tokens all later tasks use: `--color-primary: #1e3a5f`, `--color-primary-light: #2d5a8e`, `--color-primary-dark: #0f1f33`, `--gradient-hero`, `--gradient-primary`, `--shadow-card`, `--shadow-card-lg`, `--radius-2xl`, `--surface-frost`, `--tint-primary`

- [ ] **Step 1: Update the primary color tokens** in `public/css/app.css` `:root`:

```css
    --color-primary: #1e3a5f;
    --color-primary-light: #2d5a8e;
    --color-primary-dark: #0f1f33;
```

- [ ] **Step 2: Add new tokens** at the end of the `:root` block (after the transitions):

```css
    /* Civic Indigo additions */
    --gradient-hero: linear-gradient(150deg, #0f1f33 0%, #1e3a5f 55%, #2d5a8e 100%);
    --gradient-primary: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
    --shadow-card: 0 2px 12px rgba(15, 31, 51, 0.06);
    --shadow-card-lg: 0 10px 30px rgba(15, 31, 51, 0.12);
    --radius-2xl: 1.25rem;
    --surface-frost: rgba(255, 255, 255, 0.85);
    --tint-primary: #eef3f9;
```

- [ ] **Step 3: Replace the 4 hardcoded old-navy rgba values.** `grep -n "rgba(26, 54, 93" public/css/app.css` shows 4 hits (form focus ring ×2, `.tax-option` checked, `.period-option` checked). Replace each `rgba(26, 54, 93,` with `rgba(30, 58, 95,` keeping the alpha.

- [ ] **Step 4: Update PWA colors.** In `resources/views/layouts/app.blade.php` line 12: `<meta name="theme-color" content="#1e3a5f">`. In `public/manifest.json`: `"theme_color": "#1e3a5f",`.

- [ ] **Step 5: Verify no stragglers and suite green**

Run: `grep -rn "1a365d" public/css/app.css public/manifest.json resources/views/layouts/app.blade.php` → no matches.
Run: `php artisan test` → all pass (pages still render 200).

- [ ] **Step 6: Commit**

```powershell
git add public/css/app.css public/manifest.json resources/views/layouts/app.blade.php
git commit -m 'feat: unify public navy tokens with the citizen portal'
```

---

### Task 2: Header — frosted bar, logo emblem, pill links, mobile menu polish

**Files:**
- Create: `tests/Feature/HomePageEnhancementsTest.php`
- Modify: `resources/views/layouts/app.blade.php:349-368` (logo fallback)
- Modify: `public/css/app.css` (`.header`, `.logo*`, `.nav-links`, mobile menu block)

**Interfaces:**
- Consumes: Task 1 tokens
- Produces: `.logo-emblem` class (layout markup, styled here)

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_emblem_tile_when_no_logo_is_uploaded(): void
    {
        $this->get('/')->assertOk()->assertSee('logo-emblem', false);
    }
}
```

- [ ] **Step 2: Run it — must fail**

Run: `php artisan test --filter=HomePageEnhancementsTest`
Expected: FAIL (`logo-emblem` not in response).

- [ ] **Step 3: Wrap the fallback icon** in `layouts/app.blade.php` (the `@else` branch of the logo):

```blade
                    @else
                        <span class="logo-emblem"><i class="fas fa-landmark"></i></span>
                    @endif
```

- [ ] **Step 4: Restyle the header in `app.css`.** Replace the `.header` rule and add emblem styles right after it:

```css
.header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background-color: var(--surface-frost);
    -webkit-backdrop-filter: blur(12px);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(30, 58, 95, 0.08);
}

.logo-emblem {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: var(--radius-lg);
    background: var(--gradient-primary);
    color: var(--color-white);
}

.logo-emblem i {
    font-size: var(--font-size-lg);
}
```

Then delete the now-redundant `.logo i { font-size: var(--font-size-2xl); }` rule.

- [ ] **Step 5: Pill nav links (desktop).** Replace the `.nav-links a` / hover rules:

```css
.nav-links a {
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--color-gray-600);
    transition: all var(--transition-base);
}

.nav-links a:not(.btn) {
    padding: var(--spacing-2) var(--spacing-4);
    border-radius: var(--radius-full);
}

.nav-links a:not(.btn):hover,
.nav-links a:not(.btn).active {
    color: var(--color-primary);
    background-color: var(--tint-primary);
}
```

- [ ] **Step 6: Mobile menu polish.** Inside the existing `@media (max-width: 768px)` block, extend the `.nav-links` rules (keep the existing show/hide transform logic) with:

```css
    .nav-links {
        border-radius: 0 0 var(--radius-2xl) var(--radius-2xl);
        padding: var(--spacing-5) var(--spacing-4) var(--spacing-6);
        border-top: 1px solid var(--color-gray-100);
    }

    .nav-links a {
        min-height: 44px;
        display: flex;
        align-items: center;
    }

    .nav-links .btn {
        width: 100%;
    }

    .nav-links.active li {
        animation: navItemIn 0.35s ease both;
    }

    .nav-links.active li:nth-child(2) { animation-delay: 0.05s; }
    .nav-links.active li:nth-child(3) { animation-delay: 0.1s; }
    .nav-links.active li:nth-child(4) { animation-delay: 0.15s; }
    .nav-links.active li:nth-child(5) { animation-delay: 0.2s; }
```

And at file scope (outside the media query):

```css
@keyframes navItemIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .nav-links.active li { animation: none; }
}
```

- [ ] **Step 7: Run tests**

Run: `php artisan test --filter=HomePageEnhancementsTest` → PASS. Then `php artisan test` → all green (SiteBrandingTest / SettingsLogoUploadTest must still pass — uploaded-logo path untouched).

- [ ] **Step 8: Commit**

```powershell
git add tests/Feature/HomePageEnhancementsTest.php resources/views/layouts/app.blade.php public/css/app.css
git commit -m 'feat: frosted header with emblem tile and pill nav'
```

---

### Task 3: Hero — gradient base, scrim, kicker, CTA, Ken Burns, swipe

**Files:**
- Modify: `tests/Feature/HomePageEnhancementsTest.php` (add 2 tests)
- Modify: `resources/views/home.blade.php:6-46` (hero markup), `:179-242` (slider JS)
- Modify: `resources/lang/en/messages.php`, `resources/lang/hi/messages.php`, `resources/lang/mr/messages.php`
- Modify: `public/css/app.css` (hero slider block)

**Interfaces:**
- Consumes: `--gradient-hero`, `--gradient-primary`, `--radius-*` tokens
- Produces: `.slide-kicker`, `.slide-bg`, `.btn-hero`, `.btn-arrow` classes; lang key `official_digital_portal`

- [ ] **Step 1: Add failing tests**

```php
    public function test_hero_shows_localized_kicker(): void
    {
        $this->get('/')->assertOk()->assertSee('Official Digital Portal');
    }

    public function test_hero_cta_uses_arrow_chip_button(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('btn-hero', false)
            ->assertSee('btn-arrow', false);
    }
```

Run: `php artisan test --filter=HomePageEnhancementsTest` → the 2 new tests FAIL.

- [ ] **Step 2: Add the lang key** to each messages file, near the top-level page strings:

`resources/lang/en/messages.php`: `'official_digital_portal' => 'Official Digital Portal',`
`resources/lang/hi/messages.php`: `'official_digital_portal' => 'आधिकारिक डिजिटल पोर्टल',`
`resources/lang/mr/messages.php`: `'official_digital_portal' => 'अधिकृत डिजिटल पोर्टल',`

- [ ] **Step 3: Rework hero markup** in `home.blade.php`. Replace the two slide branches:

```blade
            @forelse($sliders as $index => $slider)
                <div class="slide {{ $index === 0 ? 'active' : '' }}">
                    <div class="slide-bg" style="background-image: url('{{ $slider->image_url }}');"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <span class="slide-kicker">{{ __('messages.official_digital_portal') }}</span>
                        @if($slider->title)
                            <h1>{{ $slider->title }}</h1>
                        @endif
                        @if($slider->subtitle)
                            <p>{{ $slider->subtitle }}</p>
                        @endif
                        @if($slider->link && $slider->button_text)
                            <a href="{{ $slider->link }}" class="btn btn-hero btn-lg">{{ $slider->button_text }} <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span></a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="slide active">
                    <div class="slide-content">
                        <span class="slide-kicker">{{ __('messages.official_digital_portal') }}</span>
                        <h1>{{ __('messages.welcome_to') }} {{ $settings['site_name'] ?? 'Gram Panchayat' }}</h1>
                        <p>{{ $settings['site_tagline'] ?: __('messages.serving_community') }}</p>
                        <a href="{{ route('citizen.login') }}" class="btn btn-hero btn-lg">{{ __('messages.login_to_pay_tax') }} <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span></a>
                    </div>
                </div>
            @endforelse
```

(The empty-state slide loses its inline gradient — the section background provides it.)

- [ ] **Step 4: Restyle the hero block in `app.css`.** Replace the whole HERO SLIDER section with:

```css
.hero-slider {
    position: relative;
    height: 560px;
    margin-top: 70px;
    overflow: hidden;
    background: var(--gradient-hero);
}

.hero-slider::before,
.hero-slider::after {
    content: '';
    position: absolute;
    border-radius: var(--radius-full);
    pointer-events: none;
}

.hero-slider::before {
    top: -80px;
    right: -80px;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.09), transparent 65%);
}

.hero-slider::after {
    bottom: -40px;
    left: -60px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(45, 90, 142, 0.5), transparent 70%);
}

.slider-container {
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.6s ease, visibility 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slide.active {
    opacity: 1;
    visibility: visible;
}

.slide-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}

.slide.active .slide-bg {
    animation: kenburns 14s ease-out forwards;
}

@keyframes kenburns {
    from { transform: scale(1); }
    to { transform: scale(1.08); }
}

.slide-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15, 31, 51, 0.25) 0%, rgba(15, 31, 51, 0.55) 100%);
}

.slide-content {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: fit-content;
    max-width: 90%;
    text-align: center;
    color: var(--color-white);
    padding: var(--spacing-8);
    background: rgba(255, 255, 255, 0.08);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 8px 32px 0 rgba(15, 31, 51, 0.35);
    border-radius: var(--radius-2xl);
}

.slide-kicker {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-xs);
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #f5b04c;
    margin-bottom: var(--spacing-3);
}

.slide-kicker::before {
    content: '';
    width: 16px;
    height: 2px;
    background: #f5b04c;
}
```

Note: the kicker uses `#f5b04c` — the accent lightened for contrast on navy (`#d97706` itself is illegible there). This is the sanctioned kicker use of the amber accent from the Global Constraints.

```css

.slide-content h1 {
    font-size: var(--font-size-5xl);
    font-weight: 800;
    margin-bottom: var(--spacing-4);
    max-width: 800px;
    text-wrap: balance;
}

.slide-content p {
    font-size: var(--font-size-xl);
    max-width: 600px;
    margin-bottom: var(--spacing-8);
    opacity: 0.9;
}

.btn-hero {
    background: var(--gradient-primary);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: var(--color-white);
    border-radius: var(--radius-lg);
    box-shadow: 0 8px 22px rgba(15, 31, 51, 0.45);
}

.btn-hero:hover {
    transform: translateY(-2px);
    color: var(--color-white);
    box-shadow: 0 12px 28px rgba(15, 31, 51, 0.55);
}

.btn-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: var(--radius-sm);
    background: rgba(255, 255, 255, 0.18);
    font-size: 0.7rem;
}
```

Keep the existing `.slider-nav`, `.slider-btn`, `.slider-dots` rules, but replace the `.dot` active rule with an elongating pill:

```css
.dot.active,
.dot:hover {
    background-color: var(--color-white);
}

.dot.active {
    width: 22px;
}
```

(Remove the old `transform: scale(1.2)`; `.dot` keeps `transition: all`.)

- [ ] **Step 5: Responsive + reduced motion.** In `@media (max-width: 768px)`: change `.hero-slider` height to `440px`, and add `.slider-nav { display: none; }`. In `@media (max-width: 480px)`: delete the `.hero-slider { height: 450px; }` rule (keep the h1 size rule). At file scope:

```css
@media (prefers-reduced-motion: reduce) {
    .slide.active .slide-bg { animation: none; }
}
```

- [ ] **Step 6: Add touch-swipe to the slider JS** in `home.blade.php` `@push('scripts')`, after the autoplay block, inside the `if (slides.length > 1)` guard:

```js
        // Touch swipe (arrows are hidden on mobile)
        const sliderEl = document.getElementById('heroSlider');
        let touchStartX = 0;
        sliderEl.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        sliderEl.addEventListener('touchend', (e) => {
            const dx = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(dx) > 50) showSlide(currentSlide + (dx < 0 ? 1 : -1));
        }, { passive: true });
```

- [ ] **Step 7: Run tests**

Run: `php artisan test --filter=HomePageEnhancementsTest` → PASS (all 3). Then `php artisan test` → green.

- [ ] **Step 8: Commit**

```powershell
git add tests/Feature/HomePageEnhancementsTest.php resources/views/home.blade.php resources/lang public/css/app.css
git commit -m 'feat: civic indigo hero with kicker, scrim, ken burns and swipe'
```

---

### Task 4: Quick-action card (new component) + shared `.icon-chip`

**Files:**
- Modify: `tests/Feature/HomePageEnhancementsTest.php` (add 1 test)
- Modify: `resources/views/home.blade.php` (new section directly after the hero `</section>`)
- Modify: `resources/lang/{en,hi,mr}/messages.php` (4 keys)
- Modify: `public/css/app.css`

**Interfaces:**
- Consumes: tokens; routes `citizen.login`, `grievance.create`, `digital-services`
- Produces: `.icon-chip`, `.icon-chip--tint`, `.icon-chip--gradient`, `.icon-chip--danger` (used by Tasks 5–7); `.qa-card`, `.qa-item`, `.qa-item--new`; lang keys `quick_actions`, `grievance`, `certificates`, `new`

- [ ] **Step 1: Add failing test**

```php
    public function test_quick_action_card_links_to_the_four_core_tasks(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('qa-card', false)
            ->assertSee(route('grievance.create'), false)
            ->assertSee(route('digital-services'), false)
            ->assertSee('Certificates');
    }
```

Run: `php artisan test --filter=HomePageEnhancementsTest` → new test FAILS.

- [ ] **Step 2: Lang keys** (en / hi / mr):

```php
    // en
    'quick_actions' => 'Quick Actions',
    'grievance' => 'Grievance',
    'certificates' => 'Certificates',
    'new' => 'NEW',
    // hi
    'quick_actions' => 'त्वरित सेवाएं',
    'grievance' => 'शिकायत',
    'certificates' => 'प्रमाणपत्र',
    'new' => 'नया',
    // mr
    'quick_actions' => 'जलद सेवा',
    'grievance' => 'तक्रार',
    'certificates' => 'प्रमाणपत्रे',
    'new' => 'नवीन',
```

- [ ] **Step 3: Markup** in `home.blade.php`, immediately after the hero `</section>`:

```blade
    <!-- Quick Actions -->
    <section class="quick-actions" aria-label="{{ __('messages.quick_actions') }}">
        <div class="container">
            <div class="qa-card" data-reveal>
                <a href="{{ route('citizen.login') }}" class="qa-item">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-house"></i></span>
                    <span>{{ __('messages.property_tax') }}</span>
                </a>
                <a href="{{ route('citizen.login') }}" class="qa-item">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-droplet"></i></span>
                    <span>{{ __('messages.water_tax') }}</span>
                </a>
                <a href="{{ route('grievance.create') }}" class="qa-item qa-item--new" data-badge="{{ __('messages.new') }}">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-bullhorn"></i></span>
                    <span>{{ __('messages.grievance') }}</span>
                </a>
                <a href="{{ route('digital-services') }}" class="qa-item">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-file-lines"></i></span>
                    <span>{{ __('messages.certificates') }}</span>
                </a>
            </div>
        </div>
    </section>
```

(`data-reveal` is inert until Task 10 wires it up.)

- [ ] **Step 4: CSS** — add a new section after the hero block:

```css
/* ============================================
   ICON CHIP (shared) + QUICK ACTIONS
   ============================================ */
.icon-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-lg);
    font-size: 1.1rem;
    flex-shrink: 0;
    transition: transform var(--transition-base);
}

.icon-chip--tint {
    background-color: var(--tint-primary);
    color: var(--color-primary);
}

.icon-chip--gradient {
    background: var(--gradient-primary);
    color: var(--color-white);
}

.icon-chip--danger {
    background-color: #fdecec;
    color: #dc2626;
}

.quick-actions {
    position: relative;
    z-index: 2;
}

.qa-card {
    display: flex;
    max-width: 720px;
    margin: calc(-1 * var(--spacing-10)) auto 0;
    padding: var(--spacing-4) var(--spacing-2);
    background-color: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-card-lg);
}

.qa-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-2);
    font-size: var(--font-size-xs);
    font-weight: 600;
    color: var(--color-gray-700);
    text-align: center;
    position: relative;
    padding: var(--spacing-1);
}

.qa-item:hover {
    color: var(--color-primary);
}

.qa-item:hover .icon-chip,
.qa-item:active .icon-chip {
    transform: scale(0.94);
}

.qa-item--new::after {
    content: attr(data-badge);
    position: absolute;
    top: -6px;
    right: 12%;
    padding: 2px 6px;
    background-color: var(--color-accent);
    color: var(--color-white);
    font-size: 0.55rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    border-radius: var(--radius-full);
}

@media (prefers-reduced-motion: reduce) {
    .icon-chip { transition: none; }
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=HomePageEnhancementsTest` → PASS. `php artisan test` → green.

- [ ] **Step 6: Commit**

```powershell
git add tests/Feature/HomePageEnhancementsTest.php resources/views/home.blade.php resources/lang public/css/app.css
git commit -m 'feat: quick-action card with shared icon chips'
```

---

### Task 5: Stats strip

**Files:**
- Modify: `tests/Feature/HomePageEnhancementsTest.php` (add 1 test)
- Modify: `resources/views/home.blade.php:48-82`
- Modify: `public/css/app.css` (stats section + responsive blocks)

**Interfaces:**
- Consumes: `.icon-chip--tint`, tokens
- Produces: `.stats-strip` class on the grid

- [ ] **Step 1: Add failing test**

```php
    public function test_stats_render_as_a_strip_with_icon_chips(): void
    {
        $this->get('/')->assertOk()->assertSee('stats-strip', false);
    }
```

Run: `php artisan test --filter=HomePageEnhancementsTest` → FAILS.

- [ ] **Step 2: Markup.** In the stats section: change `<div class="stats-grid">` to `<div class="stats-grid stats-strip" data-reveal>` and convert each card's bare icon to a chip, e.g.:

```blade
                <div class="stat-card">
                    <span class="icon-chip icon-chip--tint"><i class="fas fa-users"></i></span>
                    <div class="stat-content">
                        <span class="stat-number" data-target="5000">0</span>
                        <span class="stat-label">{{ __('messages.citizens_served') }}</span>
                    </div>
                </div>
```

Same chip treatment for `fa-file-invoice-dollar`, `fa-check-circle`, `fa-headset` cards. Content/`data-target`s unchanged.

- [ ] **Step 3: CSS.** Replace the STATS SECTION block:

```css
.stats-section {
    padding: var(--spacing-10) 0 var(--spacing-12);
    background-color: var(--color-gray-50);
}

.stats-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    background-color: var(--color-white);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-card);
    padding: var(--spacing-2) 0;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    padding: var(--spacing-4) var(--spacing-5);
    border-right: 1px solid var(--color-gray-100);
}

.stat-card:last-child {
    border-right: none;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: var(--font-size-2xl);
    font-weight: 800;
    color: var(--color-primary);
    font-variant-numeric: tabular-nums;
}

.stat-label {
    font-size: var(--font-size-xs);
    color: var(--color-gray-500);
}
```

(The old `.stat-card i` rule is deleted with this replacement.)

- [ ] **Step 4: Responsive.** Remove `.stats-grid` from the `@media (max-width: 1024px)` block. In `@media (max-width: 768px)` add:

```css
    .stats-strip {
        grid-template-columns: repeat(2, 1fr);
    }

    .stat-card:nth-child(2n) {
        border-right: none;
    }

    .stat-card:nth-child(-n+2) {
        border-bottom: 1px solid var(--color-gray-100);
    }
```

In `@media (max-width: 480px)` delete the old `.stats-grid { grid-template-columns: 1fr; }` rule. (Spec said 2×2 under 480px; applying it from 768px down because four 150px cells between 480–768px are cramped — noted deviation, same layout intent.)

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=HomePageEnhancementsTest` → PASS. `php artisan test` → green.

- [ ] **Step 6: Commit**

```powershell
git add tests/Feature/HomePageEnhancementsTest.php resources/views/home.blade.php public/css/app.css
git commit -m 'feat: stats as a single strip with icon chips'
```

---

### Task 6: Services — eyebrow, View All, app-style mobile rows

**Files:**
- Modify: `tests/Feature/HomePageEnhancementsTest.php` (add 1 test)
- Modify: `resources/views/home.blade.php:84-118`
- Modify: `public/css/app.css` (section header + services)

**Interfaces:**
- Consumes: `.icon-chip--gradient`, `.icon-chip--danger`, tokens
- Produces: `.section-eyebrow`, `.section-link`, `.service-body`, `.service-go`, `.service-cta` classes

- [ ] **Step 1: Add failing test**

```php
    public function test_services_section_has_eyebrow_and_view_all_link(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('section-eyebrow', false)
            ->assertSee(__('messages.view_all'));
    }
```

Run: `php artisan test --filter=HomePageEnhancementsTest` → FAILS.

- [ ] **Step 2: Markup.** Replace the services section content:

```blade
    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header" data-reveal>
                <span class="section-eyebrow">{{ __('messages.services') }}</span>
                <h2>{{ __('messages.our_services') }}</h2>
                <p>{{ __('messages.services_description') }}</p>
                <a href="{{ route('digital-services') }}" class="section-link">{{ __('messages.view_all') }} <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="services-grid">
                @foreach($taxTypes as $taxType)
                    @if(strtolower(trim($taxType->name)) === 'house tax')
                        @continue
                    @endif
                    <a href="{{ route('citizen.login') }}" class="service-card" data-reveal>
                        <span class="icon-chip icon-chip--gradient"><i class="fas {{ $taxType->icon ?? 'fa-receipt' }}"></i></span>
                        <div class="service-body">
                            <h3>{{ $taxType->name }}</h3>
                            <p>{{ $taxType->description }}</p>
                        </div>
                        <span class="service-cta">{{ __('messages.login_to_pay') }}</span>
                        <span class="service-go"><i class="fas fa-chevron-right"></i></span>
                    </a>
                @endforeach

                <!-- Grievance Redressal Card -->
                <a href="{{ route('grievance.create') }}" class="service-card" data-reveal>
                    <span class="icon-chip icon-chip--danger"><i class="fas fa-bullhorn"></i></span>
                    <div class="service-body">
                        <h3>{{ __('messages.grievance_redressal') }}</h3>
                        <p>{{ __('messages.grievance_description') }}</p>
                    </div>
                    <span class="service-cta">{{ __('messages.report_issue') }}</span>
                    <span class="service-go"><i class="fas fa-chevron-right"></i></span>
                </a>
            </div>
        </div>
    </section>
```

(The whole card is now the link — the old inline-styled red icon div and `.btn-outline` buttons are gone; the grievance red identity moves to `.icon-chip--danger`.)

- [ ] **Step 3: CSS.** Add to the SECTION STYLES block:

```css
.section-eyebrow {
    display: inline-block;
    font-size: var(--font-size-xs);
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--color-primary-light);
    margin-bottom: var(--spacing-2);
}

.section-link {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-top: var(--spacing-3);
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--color-primary-light);
}

.section-link i {
    font-size: 0.7rem;
    transition: transform var(--transition-base);
}

.section-link:hover i {
    transform: translateX(3px);
}
```

Replace the SERVICES SECTION block's card styles:

```css
.service-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--spacing-3);
    padding: var(--spacing-8);
    background-color: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: var(--radius-2xl);
    transition: all var(--transition-base);
    color: inherit;
}

.service-card:hover {
    border-color: var(--color-primary-light);
    box-shadow: var(--shadow-card-lg);
    transform: translateY(-4px);
    color: inherit;
}

.service-card .icon-chip {
    width: 56px;
    height: 56px;
    font-size: 1.4rem;
    border-radius: var(--radius-xl);
}

.service-card h3 {
    font-size: var(--font-size-xl);
    font-weight: 600;
    color: var(--color-gray-900);
}

.service-card p {
    font-size: var(--font-size-sm);
    color: var(--color-gray-500);
}

.service-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-2) var(--spacing-5);
    border: 1px solid var(--color-primary);
    border-radius: var(--radius-full);
    color: var(--color-primary);
    font-size: var(--font-size-sm);
    font-weight: 500;
    margin-top: auto;
}

.service-card:hover .service-cta {
    background-color: var(--color-primary);
    color: var(--color-white);
}

.service-go {
    display: none;
    color: var(--color-gray-400);
}
```

(Delete the old `.service-icon` rules — dead after the markup change. Also delete `.service-pricing`, `.price-label`, and `.price` — verified used by no view; `welcome.blade.php` references `service-card` but is not routed anywhere.)

- [ ] **Step 4: Mobile rows.** In `@media (max-width: 768px)` add:

```css
    .services-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-3);
    }

    .service-card {
        flex-direction: row;
        text-align: left;
        align-items: center;
        padding: var(--spacing-4);
        gap: var(--spacing-4);
    }

    .service-card .icon-chip {
        width: 46px;
        height: 46px;
        font-size: 1.1rem;
    }

    .service-card h3 {
        font-size: var(--font-size-base);
    }

    .service-card p {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .service-body {
        flex: 1;
        min-width: 0;
    }

    .service-cta {
        display: none;
    }

    .service-go {
        display: inline-flex;
    }

    .service-card:hover {
        transform: none;
    }
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --filter=HomePageEnhancementsTest` → PASS. `php artisan test` → green.

- [ ] **Step 6: Commit**

```powershell
git add tests/Feature/HomePageEnhancementsTest.php resources/views/home.blade.php public/css/app.css
git commit -m 'feat: services with eyebrow, view-all and app-style mobile rows'
```

---

### Task 7: Feature tiles

**Files:**
- Modify: `resources/views/home.blade.php:120-158`
- Modify: `public/css/app.css` (WHY US block + responsive)

**Interfaces:**
- Consumes: `.icon-chip--tint`
- Produces: `.feature-chip` class

- [ ] **Step 1: Markup.** In the why-us section: add `data-reveal` to the `section-header` div and each `feature-card`; replace each `<div class="feature-icon"><i class="fas fa-bolt"></i></div>` with:

```blade
                    <span class="icon-chip icon-chip--tint feature-chip"><i class="fas fa-bolt"></i></span>
```

(Same for `fa-shield-alt`, `fa-receipt`, `fa-history`.)

- [ ] **Step 2: CSS.** Replace the `.feature-icon` rules in the WHY US block with:

```css
.feature-chip {
    width: 52px;
    height: 52px;
    font-size: 1.25rem;
    border-radius: var(--radius-xl);
    margin-bottom: var(--spacing-4);
}

.feature-card {
    text-align: center;
    padding: var(--spacing-6);
    border-radius: var(--radius-2xl);
    transition: background-color var(--transition-base);
}

.feature-card:hover {
    background-color: var(--color-gray-50);
}
```

In `@media (max-width: 480px)`: change `.features-grid { grid-template-columns: 1fr; }` to `repeat(2, 1fr)` and add:

```css
    .feature-card {
        padding: var(--spacing-4) var(--spacing-2);
    }

    .feature-card h3 {
        font-size: var(--font-size-base);
    }
```

- [ ] **Step 3: Verify + commit**

Run: `php artisan test` → green.

```powershell
git add resources/views/home.blade.php public/css/app.css
git commit -m 'feat: compact feature tiles with tint chips'
```

---

### Task 8: CTA band + footer

**Files:**
- Modify: `resources/views/home.blade.php:160-176` (add `data-reveal` to `cta-content`)
- Modify: `public/css/app.css` (CTA + FOOTER blocks)

**Interfaces:**
- Consumes: `--gradient-hero`, `--color-primary-dark`, `--gradient-primary`
- Produces: nothing new

- [ ] **Step 1: Markup:** `<div class="cta-content" data-reveal>`.

- [ ] **Step 2: CSS — CTA band:**

```css
.cta-section {
    position: relative;
    overflow: hidden;
    padding: var(--spacing-16) 0;
    background: var(--gradient-hero);
}

.cta-section::before,
.cta-section::after {
    content: '';
    position: absolute;
    border-radius: var(--radius-full);
    pointer-events: none;
}

.cta-section::before {
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.08), transparent 65%);
}

.cta-section::after {
    bottom: -50px;
    left: -50px;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(45, 90, 142, 0.5), transparent 70%);
}

.cta-content {
    position: relative;
    z-index: 1;
    text-align: center;
    color: var(--color-white);
}
```

(Keep the existing `.cta-content h2/p` and `.cta-buttons` rules; add `border-radius: var(--radius-lg);` to `.btn-white` and `.btn-outline-white`.)

- [ ] **Step 3: CSS — footer:** in the FOOTER block change `.footer` background to `var(--color-primary-dark)` and `border-top` color in `.footer-bottom` to `rgba(255, 255, 255, 0.08)`; replace `.social-links a`:

```css
.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-lg);
    color: var(--color-gray-300);
    transition: all var(--transition-base);
}

.social-links a:hover {
    background: var(--gradient-primary);
    color: var(--color-white);
}
```

- [ ] **Step 4: Verify + commit**

Run: `php artisan test` → green (LegalPagesTest and others hit footer-bearing pages).

```powershell
git add resources/views/home.blade.php public/css/app.css
git commit -m 'feat: gradient cta band and deep navy footer'
```

---

### Task 9: Bottom nav — frosted, pill active, amber dot, safe area

**Files:**
- Modify: `public/css/app.css` (BOTTOM NAVIGATION block + the two padding rules that offset it)

**Interfaces:**
- Consumes: `--surface-frost`, `--tint-primary`, `--color-accent`
- Produces: nothing new (markup unchanged)

- [ ] **Step 1: Replace the BOTTOM NAVIGATION BAR block:**

```css
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1100;
    display: flex;
    align-items: stretch;
    justify-content: space-around;
    background-color: rgba(255, 255, 255, 0.94);
    -webkit-backdrop-filter: blur(12px);
    backdrop-filter: blur(12px);
    border-top: 1px solid var(--color-gray-100);
    padding: 6px 8px calc(6px + env(safe-area-inset-bottom, 0px));
}

.bottom-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    color: var(--color-gray-500);
    text-decoration: none;
    font-size: 0.65rem;
    font-weight: 500;
    letter-spacing: 0.02em;
    padding: 4px;
}

.bottom-nav-item i {
    font-size: 1.15rem;
    line-height: 1;
    padding: 5px 16px;
    border-radius: var(--radius-full);
    transition: background-color 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color var(--transition-fast);
}

.bottom-nav-item span {
    font-size: 0.65rem;
    font-weight: 500;
    line-height: 1;
}

.bottom-nav-item.active,
.bottom-nav-item:hover {
    color: var(--color-primary);
    text-decoration: none;
}

.bottom-nav-item.active i {
    background-color: var(--tint-primary);
}

.bottom-nav-item.active::after {
    content: '';
    width: 4px;
    height: 4px;
    border-radius: var(--radius-full);
    background-color: var(--color-accent);
    margin-top: 1px;
}

@media (prefers-reduced-motion: reduce) {
    .bottom-nav-item i { transition: none; }
}
```

(The old `height: 62px`, `::before` top bar, and icon `translateY` rules are deleted by this replacement.)

- [ ] **Step 2: Update the offsets.** `.main-content { padding-bottom: calc(70px + env(safe-area-inset-bottom, 0px)); }` and in the mobile footer rule: `padding-bottom: calc(var(--spacing-8) + 70px + env(safe-area-inset-bottom, 0px));`. Keep the desktop `@media (min-width: 769px)` reset as is.

- [ ] **Step 3: Verify + commit**

Run: `php artisan test` → green.

```powershell
git add public/css/app.css
git commit -m 'feat: frosted bottom nav with pill active state and safe-area inset'
```

---

### Task 10: Scroll reveals, sw cache bump, full verification

**Files:**
- Modify: `resources/views/home.blade.php` (reveal script; `data-reveal` attrs were added in Tasks 4–8)
- Modify: `public/css/app.css` (reveal styles)
- Modify: `public/sw.js:18` (VERSION bump)

**Interfaces:**
- Consumes: `data-reveal` attributes from Tasks 4–8
- Produces: `html.reveal-ready` gate, `.revealed` state

- [ ] **Step 1: Reveal CSS** (end of app.css):

```css
/* ============================================
   SCROLL REVEALS
   ============================================ */
html.reveal-ready [data-reveal] {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

html.reveal-ready [data-reveal].revealed {
    opacity: 1;
    transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
    html.reveal-ready [data-reveal] {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
```

(The `html.reveal-ready` gate means no-JS visitors never see hidden content.)

- [ ] **Step 2: Reveal script** in `home.blade.php` `@push('scripts')`, after the counter block:

```js
    // Scroll reveals (gated so content is never hidden without JS)
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length && 'IntersectionObserver' in window
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.classList.add('reveal-ready');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -5% 0px' });
        revealEls.forEach((el, i) => {
            el.style.transitionDelay = `${(i % 4) * 60}ms`;
            revealObserver.observe(el);
        });
    }
```

- [ ] **Step 3: Bump the service worker cache** in `public/sw.js`: `const VERSION = 'neralgov-v2';` — returning PWA users must not get the old cached CSS.

- [ ] **Step 4: Full suite**

Run: `php artisan test` → everything green (including PwaTest).

- [ ] **Step 5: Real-browser verification** (use the `run` skill / `php artisan serve`):

- Widths 360 / 390 / 768 / 1200: hero (440px mobile), qa-card overlap, stats 2×2↔strip, services rows↔cards, feature tiles 2×2↔4-col, CTA band, footer, frosted header, bottom nav pill + amber dot.
- Locales en / hi / mr (`/language/{lang}`): kicker, quick-action labels, no overflow with Devanagari headlines.
- Slider states: 0 sliders (gradient + circles + kicker), 1 (no arrows/dots), 2+ (dots pill animation, autoplay, swipe on touch emulation).
- Chrome sanity on `/about`, `/digital-services`, grievance form, `/citizen/login`, one legal page.
- DevTools: emulate `prefers-reduced-motion` → no Ken Burns, no reveals, content visible.
- Device emulation with gesture bar → bottom nav clears it (safe-area).

- [ ] **Step 6: Commit**

```powershell
git add resources/views/home.blade.php public/css/app.css public/sw.js
git commit -m 'feat: scroll reveals and sw cache bump for civic indigo rollout'
```
