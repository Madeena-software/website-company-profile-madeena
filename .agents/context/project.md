# Project Context

## Purpose

**Madeena Company Profile** is the official corporate web platform for **PT Madeena Karya Indonesia**, an Indonesian manufacturer of Digital Direct Radiography (DDR) medical imaging systems based on Camera Coupled X-Ray Detector (CCXD) technology commercialized from Universitas Gadjah Mada (UGM) research.

The repository serves two core purposes:
1. **Public Marketing and Clinical/Academic Portal**: Showcases DDR product catalog, Elsevier/Nature-style academic research articles (`/artikel`), custom company profile pages (`/halaman`), and real-time event guestbook/exhibition feedback (`/events/{slug}`).
2. **Administrative Content Management System**: An elderly-friendly, drag-and-drop CMS powered by Filament v5, supporting dynamic multilingual homepages with draft/published lifecycles, language registry management, page publication gating with admin previews, and scientific article composition with KaTeX math rendering.

## Current Technology

| Layer | Technology | Version / Notes |
|---|---|---|
| Backend | PHP / Laravel | PHP 8.4 / Laravel 13.x |
| CMS / Admin Panel | Filament PHP / Livewire 3 | 5.x |
| Frontend | Tailwind CSS + Alpine.js | Tailwind CSS 4.0 / Alpine.js 3.15 |
| Academic Typesetting | KaTeX | 0.17 (NPM) |
| Asset Bundling | Vite | 6.x |
| Database | MySQL | 8.4 |
| Object Storage | MinIO (AWS S3-compatible) | `league/flysystem-aws-s3-v3` |
| SSO Authentication | Madeena IAM / OAuth2 | `socialiteproviders/laravelpassport` |
| Application Server | Nginx + PHP 8.4-FPM | Alpine multi-stage Docker container |
| Orchestration | Docker Swarm | Stack deployment via GitHub Actions runner |
| Continuous Integration | GitHub Actions | Workflows under `.github/workflows/` |
| Local Server Port | `8011` | `composer dev` executes `php artisan serve --port=8011` |

## Repository / Branch Workflow

- **`main`**: Production release source branch. Branch HEAD must not be assumed to equal the currently deployed live production SHA (live production state must be verified through GitHub Actions deployment/diagnostic evidence). As an operational snapshot, `main` at `009b1a6` had deployment run #32432823917 cancelled, while last confirmed live deploy was run #32380250857 at `e823ea2`. Never commit directly to `main` and never force-push.
- **`develop`**: Active integration and development branch. All development and delivery tasks operate on `develop`.
- **Delivery Governance**: Controlled by the canonical `.agents/AGENTS.md` contract and `.agents/software-workflow.md`.
- **Deployment**: Repository-controlled GitHub Actions deployment workflow (`.github/workflows/deploy-swarm.yml`) executed on self-hosted Swarm runners via manual dispatch (`workflow_dispatch`). Developers and agents do not execute direct manual SSH or production mutations.

## Authentication and Roles

- **Authentication Providers**:
  - **Madeena IAM SSO**: OAuth2 flow via `/sso/redirect`, `/sso/silent`, and `/sso/callback`. Matches or auto-provisions local `User` records with `sso_id`.
  - **Local Credentials**: Standard Filament login form using email and bcrypt password.
  - **Local/Testing Bypass**: Route `/test-support/login` logs in the configured test administrator in `local` or `testing` environments only. (Production test-login bypass is completely removed).
- **User Roles & Policies**:
  - **`admin`**: Full access to all CMS resources, Homepage Editor, Language Registry, Site Settings, Pages, Products, Events, Guest Messages, and User Management.
  - **`user`**: Restricted panel user. Can access the Filament panel and create/manage **their own** posts only (`PostPolicy` enforces `user_id` ownership for update/delete; all other resources are hidden).

## Public Route Map

| Route Pattern | Method | Description | Auth / Access |
|---|---|---|---|
| `/` | `GET` | Homepage for default language (`Language::getDefault()`, e.g. `id`) | Public |
| `/{locale}` | `GET` | Localized homepage for active language (e.g. `/en`). Redirects to `/` if default language. | Public (Active languages) / Admin (`?preview=true` for inactive) |
| `/en` | `GET` | Convenience/compatibility route for English homepage (`HomeController::indexEn()` -> `localizedHome('en')`) | Public |
| `/artikel` | `GET` | Paginated article and research blog index | Public |
| `/artikel/{post:slug}` | `GET` | Individual article detail page with academic typesetting | Public (requires `is_published=true`) |
| `/produk/{product:slug}` | `GET` | Individual product detail page | Public (requires `is_active=true`) |
| `/halaman/{page:slug}` | `GET` | Custom static page (Page Builder) | Public (`is_published=true`) / Admin (`?preview=true`) |
| `/events/{event:slug}/feedback` | `GET` | Event feedback form | Public (requires `is_active=true`) |
| `/events/{event:slug}/feedback/csrf-token` | `GET` | CSRF token refresh endpoint (`no-store`, rate-limited) | Public (requires `is_active=true`) |
| `/events/{event:slug}/feedback` | `POST` | Event feedback submission (rate-limited, honeypot, duplicate check) | Public (requires `is_active=true`) |
| `/events/{event:slug}/display` | `GET` | Real-time Livewire event display for exhibition screens | Public (displays visible messages for existing event; not active-gated) |
| `/inabuyer2026/feedback` | `GET` | Legacy redirect to `/events/inabuyer-2026/feedback` | Public |
| `/inabuyer2026/display` | `GET` | Legacy redirect to `/events/inabuyer-2026/display` | Public |
| `/storage/{path}` | `GET` | Public storage proxy streaming assets from S3 `public` disk | Public |
| `/health` | `GET` | Database connectivity health check (JSON response) | Public / Monitoring |
| `/up` | `GET` | Built-in Laravel application health route | Public / Infrastructure |
| `/admin` | `GET` | Filament CMS administrative portal | Authenticated (`admin` / `user`) |
| `/sso/redirect` | `GET` | Redirects visitor to Madeena IAM login (`prompt=login`) | Public |
| `/sso/silent` | `GET` | Silent SSO check redirecting to Madeena IAM with `prompt=none` | Public |
| `/sso/callback` | `GET` | Handles OAuth2 callback from Madeena IAM | Public |
| `/test-support/login` | `GET` | Automated test support login | `local` / `testing` environments only |

## Core Domain Models

- **`Language`**: Dynamic multilingual registry. Fields: `code` (immutable unique slug, regex `^[a-z]{2,3}(-[a-z0-9]{2,4})?$`), `name`, `native_name`, `ui_labels` (JSON array of interface translations), `is_active`, `is_default`, `sort_order`. Enforces that the default language cannot be deactivated or deleted.
- **`Setting`**: Centralized key-value and JSON configuration store. Manages site metadata, branding, SEO, contact info, floating WhatsApp config, and localized homepage blocks (`homepage_sections`, `homepage_sections_draft`, `homepage_sections_{code}`, `homepage_sections_{code}_draft`).
- **`Product`**: Commercial product catalog. Fields: `name`, `slug`, `tagline`, `specifications` (JSON key-value), `content_json` (Filament Builder blocks), `image_path`, `is_featured`, `is_active`, `sort_order`.
- **`Post`**: Research and news articles. Fields: `user_id`, `title`, `slug`, `excerpt`, `content_json` (Tiptap Academic JSON), `abstract`, `keywords` (JSON array), `authors_info` (JSON array), `content_language` (e.g. `id`, `en`), `enable_auto_numbering`, `cover_image`, `category` (topic string), `placement` (homepage section placement string), `is_published`, `published_at`. (Note: Categorization is stored directly on `Post`; no normalized `Category` table exists).
- **`Page`**: Custom static pages. Fields: `title`, `slug`, `content_json` (Builder blocks), `content_language`, `enable_auto_numbering`, `show_in_header`, `show_in_footer`, `summary`, `is_published`, `published_at`.
- **`Event`**: Generic event entity. Fields: `name`, `slug`, `description`, `is_active`, `starts_at`, `ends_at`. Controls public availability of the feedback collection endpoints.
- **`GuestMessage`**: Event visitor impressions and messages. Fields: `event_id`, `name`, `organization`, `position`, `phone`, `email`, `kesan_dan_pesan`, `is_visible`.
- **`User`**: Admin and author accounts. Fields: `sso_id`, `name`, `email`, `password`, `role`. `isAdmin()` returns true if `role === 'admin'` or matches configured admin email.

## CMS Resources

- **`HomepageEditor`** (`/admin/homepage-editor`): Full-width builder page for constructing homepage sections across registered languages. Supports draft saving, previewing, live publishing (`Update Prod`), and language-to-language structure duplication. Admin only.
- **`LanguageResource`** (`/admin/languages`): CRUD management for the dynamic language registry, activation status, UI translation labels, and default language setting. Admin only.
- **`ProductResource`** (`/admin/products`): Product catalog management, KeyValue technical specs, and multi-block detail page construction. Admin only.
- **`PostResource`** (`/admin/posts`): Article authoring using the Academic RichEditor with custom blocks (Figure, Table, Equation, Reference List), auto-numbering, abstract, keywords, and co-authors. Admins manage all posts; Users manage own posts.
- **`PageResource`** (`/admin/pages`): Static page management using Builder blocks, publication gating (`Publish` / `Unpublish`), and admin draft preview links. Admin only.
- **`EventResource`** (`/admin/events`): Management of corporate/exhibition events and their active status. Admin only.
- **`GuestMessageResource`** (`/admin/guest-messages`): Moderation of attendee feedback with live display visibility toggles. Admin only.
- **`SiteSettings`** (`/admin/site-settings`): Global site settings for contact information, social links, SEO tags, custom header navigation links, dynamic branding (logo, theme colors, typography), and floating WhatsApp contact button. Admin only.
- **`UserResource`** (`/admin/users`): User account and role administration. Admin only.

## Homepage Architecture

- **Data-Driven Registry**: Homepage localization is powered by dynamic `Language` records rather than hardcoded locales.
- **Key-Value Storage Strategy (Indonesian Legacy Invariant)**:
  - Indonesian (`id`) **permanently** owns the unsuffixed legacy keys: `homepage_sections` (published live) and `homepage_sections_draft` (draft).
  - Every non-Indonesian language **always** uses language-scoped suffixed keys: `homepage_sections_{code}` (published live) and `homepage_sections_{code}_draft` (draft) (e.g. `homepage_sections_en`, `homepage_sections_ja`).
  - This storage separation is tied strictly to the language code (`id` vs non-`id`), **not** to the dynamic concept of `is_default`. Even if Japanese (`ja`) or English (`en`) is designated as `is_default = true`, `ja` still uses `homepage_sections_ja` / `homepage_sections_ja_draft`, while `id` continues to use `homepage_sections` / `homepage_sections_draft`.
- **Draft vs. Published Workflow**:
  - `💾 Simpan Draft`: Persists current editor state to `homepage_sections_draft` (for `id`) or `homepage_sections_{code}_draft` (for non-`id`).
  - `🚀 Update Prod`: Copies draft content into `homepage_sections` (for `id`) or `homepage_sections_{code}` (for non-`id`) to make it live for public visitors.
- **Language Visibility & Private Preview**:
  - Public visitors can only access active languages (`Language->is_active = true`). Inactive languages return 404.
  - Authenticated administrators can preview inactive or draft language homepages using `?preview=true`.
  - Public language switcher renders only active languages.
- **Duplication Workflow (`duplicateToLanguage`)**:
  - Allows copying homepage layout and content from the active editor language to another registered target language.
  - Resolves source content: uses persisted draft if available, falling back to published content.
  - Writes **only** to the target language's draft key (`homepage_sections_{target}_draft`).
  - Target Protection: Blocks duplication if the target language already has draft or published content to prevent accidental overwrites.
  - Duplication copies section structure; it does not perform automatic translation.
- **Dynamic UI Labels**: UI strings (e.g. `navigation`, `contact`, `all_rights_reserved`, `language`, `articles`) are stored in `Language->ui_labels` and rendered via `$language->getUiLabel('key')`, allowing interface translations without code changes.

## Page Lifecycle

- **Draft State**: Newly created pages default to `is_published = false` and `published_at = null`.
- **Publication Gating**:
  - `Publikasikan`: Sets `is_published = true` and `published_at = now()`, making `/halaman/{slug}` publicly accessible.
  - `Batal Publikasi`: Sets `is_published = false` and `published_at = null`, returning 404 to unauthenticated/public visitors.
- **Admin Preview**: Authenticated administrators can preview draft pages at `/halaman/{slug}?preview=true`. Unauthenticated visitors receive a 404 response.
- **Homepage Reference Safety**: The homepage "About" section resolves referenced pages via `Page::where('is_published', true)` for public visitors, preventing draft content leakage.
- **Architectural Boundary & Known Limitation**: Page publication is a binary access gate on the model record, not a revision snapshot system. Modifying an already-published page immediately mutates the live public content in the database.

## Posts / Products

- **Academic Posts**:
  - Powered by Tiptap RichEditor with custom blocks: `FigureBlock` (auto-numbered figures with captions), `TableBlock` (HTML tables with captions), `EquationBlock` (LaTeX math with KaTeX rendering), and `ReferenceListBlock` (Elsevier/Nature-style bibliography).
  - In-text citations using `[@1]`, `[@fig-1]`, `[@tbl-1]`, `[@eq-1]` automatically generate interactive cross-reference anchor links on the frontend.
  - Categorization uses string columns `category` (topic classification) and `placement` (homepage section placement).
  - Filtered by `content_language` on homepage auto-pull sections.
- **Products**:
  - Managed with KeyValue specifications and rich Builder blocks.
  - Filtered by `is_active = true` on public listing and detail pages.

## Event Feedback & Hardening

- **Generic Multi-Event Architecture**: Replaces previous single-event logic with generic `Event` and `GuestMessage` relationships.
- **Active Event Gating**: `Event->is_active = false` immediately gates feedback collection (feedback form `GET`, CSRF refresh `GET`, and submission `POST` endpoints return 404).
- **Event Live Display**: `/events/{slug}/display` uses Livewire to stream `is_visible = true` messages to presentation screens with auto-escaped HTML. It remains reachable for any valid event route regardless of `is_active` (active-gating display is deferred). Submissions default to `is_visible = true` (pre-submission moderation queue is deferred).
- **Submission Hardening**:
  - **CSRF Token Endpoint**: `GET /events/{slug}/feedback/csrf-token` provides fresh tokens with `Cache-Control: no-store` and rate limiting (60 requests/min per IP).
  - **Rate Limiting**: `POST /events/{slug}/feedback` is rate-limited to 30 requests/min per IP, and 3 submissions per 10 minutes per contact identity (SHA-256 fingerprint of normalized email, phone, or name/organization).
  - **Passive Honeypot**: Hidden `website` field silently discards bot submissions without throwing errors.
  - **Duplicate Suppression**: Re-submissions matching the same name, organization, message, and contact info within a 2-minute window are silently suppressed.

## Storage

- **Object Storage Backend**: S3-compatible MinIO instance configured via Laravel filesystem disks (`public` and `enterprise_backups`).
- **Public File Delivery**: Handled through `PublicStorageController` (`/storage/{path}`), ensuring persistent file delivery regardless of container host filesystem state.
- **Automated Backups**: Custom `backup:upload` command uploads compressed `.sql.gz` database dumps to the S3 backup bucket with `.sha256` integrity verification manifests and automatic 14-day retention pruning.

## Deployment Architecture

- **Docker Swarm Stack**: Composed of `app` (PHP 8.4-FPM), `queue` (artisan worker), `nginx` (web proxy publishing host port 8011), and `db` (MySQL 8.4).
- **Production Port**: Published web port is `8011`.
- **CI/CD Pipeline**: Continuous integration executes on GitHub-hosted `ubuntu-latest` for all pushes to `develop` and pull requests targeting `main`. Swarm deployment (`.github/workflows/deploy-swarm.yml`) remains strictly isolated on self-hosted runners via manual dispatch (`workflow_dispatch`).

## Testing / Quality Gates

- **Test Suite**: PHPUnit 11 test suite located under `tests/`.
- **Quality & Verification Gates**:
  - `ReleaseSmokeTest`: High-level feature smoke test covering 11 critical anonymous/public and route contracts (`/health`, `/up`, homepage, localized homepage, `/artikel`, article detail, `/produk`, `/halaman`, event feedback, event display, and testing-support login boundary).
  - `LanguageRegistryTest`: Language code validation, default language constraints, UI label fallbacks.
  - `HomepageEditorTest`: Section loading, draft saving, live publishing, and cross-language duplication protection.
  - `HomeControllerTest`: Public homepage rendering, language resolution, preview permissions, and auto-pull filtering.
  - `PagePublicationTest`: Draft 404 gating, admin preview access, homepage about reference safety, and unpublishing.
  - `EventFeedbackTest`: Active/inactive event gating, CSRF token endpoint, IP/contact rate limits, honeypot discarding, duplicate suppression, and display escaping.
  - `PostResourceTest` & `ProductResourceTest`: CRUD authorization, RBAC ownership scoping.
  - `PublicStorageRouteTest`: Storage streaming and path traversal protection.
  - `Pint Quality Ratchet` (`scripts/pint-ratchet.sh`): Enforces zero code style violations on all PHP files added or touched since baseline `6f6ec58662f6e5b8db3fe6ecf9b6aa281da50f87`.
  - `Reusable HTTP Smoke Script` (`scripts/http-smoke.sh`): Safe, read-only GET endpoint verification for post-deployment release gate validation against explicit target URLs.
  - `Localhost HTTP Smoke in CI`: Executes ephemeral server and migration against a temporary SQLite database to verify live HTTP response contracts prior to merge.
  - `Dependency Security Baseline`: Audited in `docs/security/dependency-audit-2026-08-22.md` with release disposition CLEAR (#10A accepted baseline `46ad48d41c57737f5139fb61c64132fbe0203219`). Remediated on `develop` in Task #10B (`docs/security/dependency-remediation-2026-08-26.md`) with 0 known npm vulnerabilities and 0 Composer security advisories / abandoned packages. Final Release Gate remains pending.

## Current Known Technical Debt / Deferred Scope

- **Repository-Wide Historical Pint Formatting Debt**: 49 pre-existing PHP files at baseline `6f6ec58662f6e5b8db3fe6ecf9b6aa281da50f87` have styling debt. Unchanged files are deferred for a dedicated repository-wide formatting task; new and touched files are strictly guarded by the Pint ratchet.
- **Translation Groups Entity Linking**: Articles, Products, and Pages do not yet have linked translation group IDs connecting equivalent language variants.
- **Localized URL Route Groups**: Pages, Articles, and Products currently use single top-level route patterns (`/halaman/{slug}`, `/artikel/{slug}`, `/produk/{slug}`) rather than localized prefix route groups (e.g. `/en/articles/{slug}`).
- **Page/Product Revision Snapshots**: Editing an already-published Page or Product mutates the live database record directly; separate draft revision snapshots for pages are deferred.
- **Dormant Page Navigation Attributes**: `Page` attributes `show_in_header` and `show_in_footer` are not currently consumed by public navigation (public navigation is driven by homepage sections and `nav_custom_links`).
- **Event Display Active Gating**: Gating the `/events/{slug}/display` screen behind `Event->is_active` is deferred; it currently remains reachable for all valid event routes.
- **Event Message Pre-Moderation**: Guest messages default to `is_visible = true` upon submission; an administrative approval queue prior to display is deferred.
