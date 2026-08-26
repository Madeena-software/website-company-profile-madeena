# Dependency Security Remediation — 2026-08-26

## Baseline

- **develop**: `5f06b0aeacedf42a102a903fe7d048acf10403c7`
- **main**: `009b1a65e1216d8c097606c51019b3947d2ba574`

---

## Before

### npm Audit Pre-State
- **Vulnerable Package Entries**: 8 package entries
- **Distinct Security Advisories**: 42 unique advisory IDs
- **Severity Breakdown**:
  - Critical: 1 (`shell-quote`)
  - High: 18 (`axios` [10], `nanoid` [2], `postcss` [2], `form-data` [1], `shell-quote` [1], `vite` [2])
  - Moderate: 22 (`axios` [17], `follow-redirects` [1], `postcss` [2], `vite` [2])
  - Low: 1 (`axios` [1])
- **Production-Tree (`--omit=dev`) Vulnerabilities**: 0

### Composer Audit Pre-State
- **Affected Packages**: 18 packages in production `composer.lock`
- **Distinct Advisories**: 46 advisories
- **Severity Breakdown**:
  - Critical: 1 (`mtdowling/jmespath.php`)
  - High: 9 (`filament/filament` [3], `guzzlehttp/guzzle` [1], `laravel/framework` [1], `league/commonmark` [4], `symfony/http-kernel` [1], `symfony/mime` [1])
  - Medium: 30 (`filament/actions` [1], `filament/infolists` [1], `filament/tables` [1], `guzzlehttp/guzzle` [8], `guzzlehttp/psr7` [4], `laravel/framework` [1], `league/commonmark` [2], `phpseclib/phpseclib` [1], `symfony/html-sanitizer` [4], `symfony/http-foundation` [1], `symfony/mailer` [1], `symfony/mime` [1], `symfony/routing` [2])
  - Low: 5 (`symfony/html-sanitizer` [1], `symfony/polyfill-intl-idn` [1], `symfony/yaml` [3])
  - Untyped: 1 (`laravel/framework` [1])
- **Abandoned Packages**: 0

---

## Dependency Changes

### NPM Remediation Table

| Package | Before | After | Why |
|---|---|---|---|
| `axios` | `1.13.6` | `1.20.0` | Remediates 28 security advisories (SSRF bypass, prototype pollution, stream limits, credentials leakage) |
| `concurrently` | `9.2.1` | `9.2.4` | Direct devDependency update for CLI tooling and pulling patched `shell-quote` |
| `follow-redirects` | `1.15.11` | `1.16.0` | Transitive closure under `axios` (GHSA-r4q5-vmmm-2653) |
| `form-data` | `4.0.5` | `4.0.6` | Transitive closure under `axios` (GHSA-hmw2-7cc7-3qxx) |
| `nanoid` | `3.3.11` | `3.3.18` | Transitive closure under `postcss` / `vite` (GHSA-28wg-ghj8-5hjv, GHSA-2v37-7h3g-55p8) |
| `postcss` | `8.5.8` | `8.5.26` | Transitive closure under `vite` (GHSA-qx2v-qp2m-jg93, GHSA-6g55-p6wh-862q, GHSA-fxqj-rqcc-2cmp, GHSA-r28c-9q8g-f849) |
| `shell-quote` | `1.8.3` | `1.9.0` | Transitive closure under `concurrently` (GHSA-w7jw-789q-3m8p [Critical], GHSA-395f-4hp3-45gv [High]) |
| `vite` | `6.4.1` | `6.4.3` | Remediates dev-server file read and traversal advisories (GHSA-4w7w-66w2-5vf9, GHSA-p9ff-h696-f583, GHSA-v6wh-96g9-6wx3, GHSA-fx2h-pf6j-xcff) |

### Composer Remediation Table

| Package | Before | After | Why |
|---|---|---|---|
| `mtdowling/jmespath.php` | `2.8.0` | `2.9.2` | Remediates Critical code execution advisory GHSA-pcw8-m77r-2528 / CVE-2026-54133 (min `>= 2.9.1`) |
| `filament/filament` | `v5.5.2` | `v5.7.6` | Remediates High MFA reuse advisory GHSA-mc5j-f6wx-h9qh and 2 Medium advisories (min `>= 5.6.5`) |
| `guzzlehttp/guzzle` | `7.10.0` | `7.15.5` | Remediates High hostname bypass advisory GHSA-v5mv-p594-2x33 and 8 Medium advisories (min `>= 7.15.2`) |
| `laravel/framework` | `v13.5.0` | `v13.29.0` | Remediates High email validation advisory GHSA-5vg9-5847-vvmq and 2 Medium/Untyped advisories (min `>= 13.10.0`) |
| `league/commonmark` | `2.8.2` | `2.10.0` | Remediates 4 High ReDoS/collision advisories and 2 Medium advisories (min `>= 2.9.0`) |
| `symfony/http-kernel` | `v8.0.8` | `v8.0.15` | Remediates High HEAD attribute filter bypass advisory GHSA-6439-2f28-8p8q (min `>= 8.0.12`) |
| `symfony/mime` | `v8.0.8` | `v8.0.15` | Remediates High email CRLF injection advisory GHSA-qpmx-3rfj-7rhv and 1 Medium advisory (min `>= 8.0.12`) |
| `filament/actions` | `v5.5.2` | `v5.7.6` | Remediates Medium scope advisory CVE-2026-48067 (min `>= 5.6.4`) |
| `filament/infolists` | `v5.5.2` | `v5.7.6` | Remediates Medium ImageEntry advisory CVE-2026-48167 (min `>= 5.6.5`) |
| `filament/tables` | `v5.5.2` | `v5.7.6` | Remediates Medium ImageColumn advisory CVE-2026-48167 (min `>= 5.6.5`) |
| `guzzlehttp/psr7` | `2.9.0` | `2.13.1` | Remediates 4 Medium PSR-7 advisories (min `>= 2.9.2`) |
| `phpseclib/phpseclib` | `3.0.53` | `3.0.57` | Remediates Medium crypto advisory CVE-2026-55599 (min `>= 3.0.54`) |
| `symfony/html-sanitizer` | `v8.0.8` | `v8.0.14` | Remediates 4 Medium and 1 Low sanitization advisories (min `>= 8.0.13`) |
| `symfony/http-foundation` | `v8.0.8` | `v8.0.15` | Remediates Medium header parsing advisory CVE-2026-48736 (min `>= 8.0.13`) |
| `symfony/mailer` | `v8.0.8` | `v8.0.15` | Remediates Medium mail transport advisory CVE-2026-45068 (min `>= 8.0.12`) |
| `symfony/polyfill-intl-idn` | `v1.36.0` | `v1.42.0` | Remediates Low IDN parsing advisory CVE-2026-46644 (min `>= 1.38.1`) |
| `symfony/routing` | `v8.0.8` | `v8.0.15` | Remediates 2 Medium URL generator advisories (min `>= 8.0.13`) |
| `symfony/yaml` | `v8.0.8` | `v8.0.15` | Remediates 3 Low YAML parser advisories (min `>= 8.0.12`) |

### Meaningful Required Transitive Closure Updates (Composer)

- `filament/*` peer components (`forms`, `notifications`, `query-builder`, `schemas`, `support`, `widgets`) aligned to `v5.7.6`.
- `guzzlehttp/*` peer components (`promises` to `2.5.3`, `uri-template` to `v2.0.1`).
- `league/flysystem` updated to `3.35.3` and `league/mime-type-detection` to `1.17.0`.
- `livewire/livewire` updated to `v4.4.2` to maintain Filament 5 compatibility.
- `symfony/*` support components (`console`, `css-selector`, `error-handler`, `event-dispatcher`, `finder`, `process`, `string`, `translation`, `var-dumper`, polyfills) aligned to `v8.0.15` / `v8.0.14` / `v1.42.0`.
- `anourvalar/eloquent-serialize` (`1.3.11`) and `symfony/polyfill-php86` (`v1.41.0`) resolved by Composer solver.

---

## After

- **`npm audit` (Full Tree)**: **0 vulnerabilities**
- **`npm audit --omit=dev` (Production Tree)**: **0 vulnerabilities**
- **`composer audit --locked`**: **0 security advisories**, **0 abandoned packages**

---

## Compatibility Verification

- **Targeted Feature Tests**:
  - `EventFeedbackTest`: 21 tests, 455 assertions — **PASS**
  - `EventDisplayTest`: 4 tests, 26 assertions — **PASS**
  - `ReleaseSmokeTest`: 11 tests, 21 assertions — **PASS**
  - `SsoTest`: 5 tests, 19 assertions — **PASS**
  - `StorageConfigTest`: 3 tests, 15 assertions — **PASS**
  - `StorageCommandsTest`: 2 tests, 10 assertions — **PASS**
  - `HomepageEditorTest`: 33 tests, 142 assertions — **PASS**
  - `LanguageRegistryTest`: 12 tests, 47 assertions — **PASS**
  - `PagePublicationTest`: 13 tests, 41 assertions — **PASS**
  - `PageResourceTest`: 4 tests, 5 assertions — **PASS**
- **Full PHPUnit Test Suite**: 166 tests, 1028 assertions — **PASS (100% OK)**
- **Frontend Build**: `npm run build` — **PASS (64 modules transformed in 4.08s)**
- **Pint Quality Ratchet**: `./scripts/pint-ratchet.sh HEAD` — **PASS (7 post-baseline files checked, 0 violations)**
- **Localhost HTTP Smoke in CI**: Verified on GitHub Actions runner.

---

## Residual Risk

No known npm or Composer advisories remain in the exact remediated lockfiles at the recorded audit timestamp (`2026-08-26 14:10:23 UTC`).

---

## Release Status

Dependency remediation completed on `develop`.
Not yet merged to `main`.
Not yet deployed.
Final Release Gate still required.
