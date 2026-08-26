# Dependency Security Audit — 2026-08-22

## Release Security Disposition

> **Remediation status**: The vulnerable lock inventory documented by this audit was remediated in Task #10B. See [dependency-remediation-2026-08-26.md](dependency-remediation-2026-08-26.md).

**CLEAR — NO DEPENDENCY SECURITY BLOCKER IDENTIFIED**

- **Node/NPM Audit Scope**:
  - **Vulnerable Package Entries in Metadata**: 8 package entries (1 Moderate, 5 High, 2 Critical by package).
  - **Distinct NPM Security Advisories**: 42 unique advisory IDs (1 Critical, 18 High, 22 Moderate, 1 Low; sum = 42).
  - **Production-Tree (`--omit=dev`) Advisories**: 0 vulnerabilities (`katex@^0.17.0` has 0 advisories).
  - **Production Browser Runtime Packages**: 1 package (`axios@1.13.6`), where all 28 reported advisories are either Node.js-adapter-specific (SSRF, proxy bypass, HTTP stream headers, cloud metadata exfiltration) or require global prototype pollution gadgets that are absent in Madeena.
  - **Production Build-Time Packages**: 3 packages (`vite`, `postcss`, `nanoid`) executing in ephemeral build containers on trusted repository source files.
  - **CI / Local Dev-Only Packages**: 2 packages (`concurrently`, `shell-quote`) used solely for local developer convenience (`composer dev`).
  - **Unreachable Transitive Packages**: 2 packages (`follow-redirects`, `form-data`) stripped from browser bundles by Vite tree-shaking.
  - **Credible Production Exploit Paths**: 0.

- **Composer / PHP Locked Dependencies Scope**:
  - **Distinct Affected Packages**: 18 packages in production `composer.lock`.
  - **Distinct Advisories**: 46 advisories (1 Critical, 9 High, 30 Medium, 5 Low, 1 Untyped).
  - **Abandoned Packages**: 0.
  - **Vulnerable by Version**: YES (all 18 packages are locked at versions with upstream advisories).
  - **Critical / High Reachability**: All 10 Critical and High advisories (`mtdowling/jmespath.php`, `filament/filament`, `guzzlehttp/guzzle`, `laravel/framework`, `league/commonmark` [4], `symfony/http-kernel`, `symfony/mime`) have been analyzed against repository code and runtime configuration. Required exploit prerequisites (e.g. user-supplied JMESPath expressions, MFA recovery codes, attacker-controlled outbound request hosts, untrusted Markdown parsing, Symfony controller attributes, raw mail header construction) are absent in Madeena.
  - **Credible Production Exploit Paths Identified**: 0.

- **Remediation Plan**: Full candidate remediation matrices for both npm and Composer are established below for execution in Task #10B.

---

## 1. Audit Metadata & Toolchain

- **Audited Git Baseline SHA**: `c94688cc1e0875eca5950935a0d39b974d93a62f` (branch: `develop`)
- **Main Branch Baseline SHA**: `009b1a65e1216d8c097606c51019b3947d2ba574`
- **Audit Timestamp UTC**: `2026-08-22 04:26:20 UTC` (re-verified `2026-08-22 08:35:00 UTC`)
- **Node.js**: `v22.22.1`
- **npm**: `9.2.0`
- **PHP**: `8.5.4 (cli)` (Zend Engine v4.5.4 / OPcache v8.5.4)
- **Composer**: `2.9.5` (2026-01-29)

---

## 2. Architecture & Container Security Boundary

### Multi-Stage Container Architecture
Production containerization is defined in [Dockerfile](file:///var/www/madeena-company-profile/Dockerfile) and [.dockerignore](file:///var/www/madeena-company-profile/.dockerignore):

1. **Stage 0 (`composer-deps`)**: `php:8.4-cli` installs production PHP dependencies using `composer install --no-dev --no-scripts`.
2. **Stage 1 (`node-builder`)**: `node:24-alpine` installs dependencies (`npm ci --no-audit --no-fund`) and compiles static frontend assets (`npm run build`).
3. **Stage 2 (`base`)**: Installs Linux OS packages and PHP 8.4 extensions for `php:8.4-fpm`.
4. **Stage 3 (`app`)**: Assembles the production image:
   - Copies PHP backend code (`COPY . .`).
   - Copies `/app/vendor` from `composer-deps`.
   - Copies **only** `/app/public/build` static assets from `node-builder`.
   - **`node_modules/` is never copied into the final production image** (`.dockerignore` explicitly excludes `node_modules/`, `vendor/`, and `public/build/`).
   - Neither `node` nor `npm` binaries exist in the production runtime container.

### Execution Boundaries
- **Production Server Runtime**: 0 Node.js packages execute on the production server. Composer dependencies installed via `--no-dev` ARE physically present in production vendor (`/var/www/html/vendor`).
- **Production Browser Runtime**: Only static JS/CSS assets compiled into `public/build/` are served to web visitors.
- **Build Infrastructure Runtime**: Vite, PostCSS, and esbuild execute only during Docker image assembly or local compilation.

---

## 3. NPM Audit Summary

### Full Tree Audit (`npm audit`)
- **Vulnerable Packages in Metadata**: 8 package entries (1 Moderate, 5 High, 2 Critical by package)
- **Distinct Security Advisories**: 42 unique advisory IDs
  - Critical: 1 (`GHSA-w7jw-789q-3m8p` on `shell-quote`)
  - High: 18 (10 on `axios`, 2 on `nanoid`, 2 on `postcss`, 1 on `form-data`, 1 on `shell-quote`, 2 on `vite`)
  - Moderate: 22 (17 on `axios`, 1 on `follow-redirects`, 2 on `postcss`, 2 on `vite`)
  - Low: 1 (`GHSA-xhjh-pmcv-23jw` on `axios`)
  - Sum: 1 + 18 + 22 + 1 = 42
- **Exit Code**: `1`

### Production-Tree Audit (`npm audit --omit=dev`)
- **Total Vulnerabilities**: 0
- **Exit Code**: `0`
- **Verified**: The sole production npm dependency declared in `package.json` (`dependencies`: `"katex": "^0.17.0"`) contains 0 known vulnerabilities.

---

## 4. Complete NPM Advisory Classification (42 Distinct Advisories)

### 4.1 `axios` (Installed: `1.13.6` | 28 Distinct Advisories | Bundled in Browser JS)
All 28 advisories affect `axios@1.13.6`. In Madeena, Axios is assigned to `window.axios` in `resources/js/bootstrap.js` with default `X-Requested-With` header. In the browser runtime, Vite bundles Axios using browser `XMLHttpRequest`/`fetch`, eliminating Node-specific HTTP adapter code.

| # | Advisory ID | Severity | Vulnerable Range | Patched Range | Title / Vulnerable Mechanism | Exploit Prerequisites & Repository Evidence | Execution Category & Disposition |
|---|---|---|---|---|---|---|---|
| 1 | `GHSA-3p68-rc4w-qgx5` | Moderate | `>=1.0.0 <1.15.0` | `1.15.0` | NO_PROXY hostname normalization SSRF bypass | Requires Node HTTP adapter & `NO_PROXY` config. Browser uses native networking. | B. Browser Runtime / SAFE PATCH |
| 2 | `GHSA-w9j2-pvgh-6h63` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | Auth bypass via prototype pollution in `validateStatus` merge | Requires attacker prototype pollution gadget. No prototype pollution sources exist. | B. Browser Runtime / SAFE PATCH |
| 3 | `GHSA-pmwg-cvhr-8vh7` | High | `>=1.0.0 <1.15.1` | `1.15.1` | Incomplete fix for CVE-2025-62718 (NO_PROXY 127.0.0.0/8) | Requires Node HTTP adapter & `NO_PROXY` loopback bypass. Browser uses native networking. | B. Browser Runtime / SAFE PATCH |
| 4 | `GHSA-3w6x-2g7m-8v23` | Moderate | `>=1.0.0 <1.15.2` | `1.15.2` | JSON response tampering via prototype pollution in `parseReviver` | Requires prototype pollution gadget. No prototype pollution sources exist. | B. Browser Runtime / SAFE PATCH |
| 5 | `GHSA-xhjh-pmcv-23jw` | Low | `>=1.0.0 <1.15.1` | `1.15.1` | Null byte injection in `AxiosURLSearchParams` | Requires invoking `AxiosURLSearchParams` with unsanitized null bytes. Not used. | B. Browser Runtime / SAFE PATCH |
| 6 | `GHSA-445q-vr5w-6q77` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | CRLF injection in multipart body via `formDataToStream` | Node.js multipart stream only. Browser uses native `FormData`. | B. Browser Runtime / SAFE PATCH |
| 7 | `GHSA-m7pr-hjqh-92cm` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | `no_proxy` bypass via IP alias allows SSRF | Requires Node HTTP adapter + proxy. Browser uses native networking. | B. Browser Runtime / SAFE PATCH |
| 8 | `GHSA-5c9x-8gcm-mpgx` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | HTTP adapter streamed uploads bypass `maxBodyLength` | Requires Node.js HTTP upload streaming with `maxRedirects: 0`. | B. Browser Runtime / SAFE PATCH |
| 9 | `GHSA-vf2m-468p-8v99` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | HTTP adapter streamed responses bypass `maxContentLength` | Requires Node.js HTTP response streaming. | B. Browser Runtime / SAFE PATCH |
| 10 | `GHSA-pf86-5x62-jrwf` | High | `>=1.0.0 <1.15.1` | `1.15.1` | Prototype pollution gadgets in request/response merge | Requires global prototype pollution exploit. None exist. | B. Browser Runtime / SAFE PATCH |
| 11 | `GHSA-6chq-wfr3-2hj9` | High | `>=1.0.0 <1.15.1` | `1.15.1` | Header injection via prototype pollution | Requires global prototype pollution exploit. None exist. | B. Browser Runtime / SAFE PATCH |
| 12 | `GHSA-xx6v-rp6x-q39c` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | XSRF token leakage via prototype pollution in `withXSRFToken` | Requires prototype pollution + cross-origin Axios calls. Madeena uses same-origin CSRF only. | B. Browser Runtime / SAFE PATCH |
| 13 | `GHSA-q8qp-cvcw-x6jj` | High | `>=1.0.0 <1.15.2` | `1.15.2` | Prototype pollution read-side gadgets in HTTP adapter | Node.js HTTP adapter only. Stripped in browser bundle. | B. Browser Runtime / SAFE PATCH |
| 14 | `GHSA-fvcv-3m26-pcqx` | Moderate | `>=1.0.0 <1.15.0` | `1.15.0` | Cloud metadata exfiltration via header injection | Requires server-side Node Axios fetching attacker URLs in cloud container. | B. Browser Runtime / SAFE PATCH |
| 15 | `GHSA-62hf-57xw-28j9` | Moderate | `>=1.0.0 <1.15.1` | `1.15.1` | Unbounded recursion in `toFormData` causes DoS | Requires passing deeply nested attacker objects to `toFormData`. Not invoked with user data. | B. Browser Runtime / SAFE PATCH |
| 16 | `GHSA-hfxv-24rg-xrqf` | High | `>=1.0.0 <1.16.0` | `1.16.0` | ReDoS via cookie name injection | Requires passing crafted cookie names to Axios in Node.js. Browser manages cookies natively. | B. Browser Runtime / SAFE PATCH |
| 17 | `GHSA-777c-7fjr-54vf` | High | `>=1.7.0 <1.16.0` | `1.16.0` | Allocation of resources without throttling | Node.js buffer allocation without limits. | B. Browser Runtime / SAFE PATCH |
| 18 | `GHSA-p92q-9vqr-4j8v` | High | `>=1.0.0 <1.16.0` | `1.16.0` | `Proxy-Authorization` credential leak across HTTP-to-HTTPS redirect | Node.js HTTP adapter only. | B. Browser Runtime / SAFE PATCH |
| 19 | `GHSA-j5f8-grm9-p9fc` | High | `>=1.0.0 <1.16.0` | `1.16.0` | `Proxy-Authorization` header leak when proxy re-evaluated | Node.js HTTP adapter only. | B. Browser Runtime / SAFE PATCH |
| 20 | `GHSA-3g43-6gmg-66jw` | High | `>=1.0.0 <1.15.2` | `1.15.2` | Credential theft via prototype pollution in config merge | Requires prototype pollution gadget. None exist. | B. Browser Runtime / SAFE PATCH |
| 21 | `GHSA-35jp-ww65-95wh` | High | `>=1.0.0 <1.16.0` | `1.16.0` | MITM via prototype pollution in `config.proxy` | Requires prototype pollution setting `Object.prototype.proxy`. None exist. | B. Browser Runtime / SAFE PATCH |
| 22 | `GHSA-898c-q2cr-xwhg` | Moderate | `>=1.0.0 <1.16.0` | `1.16.0` | DoS & header injection via prototype pollution in merge | Requires prototype pollution gadget. None exist. | B. Browser Runtime / SAFE PATCH |
| 23 | `GHSA-42h9-826w-cgv3` | Moderate | `>=1.0.0 <1.18.0` | `1.18.0` | Excessive recursion in `formDataToJSON` causes DoS | Requires invoking `formDataToJSON` on crafted objects. Not used. | B. Browser Runtime / SAFE PATCH |
| 24 | `GHSA-pmv8-rq9r-6j72` | Moderate | `>=1.0.0 <1.18.0` | `1.18.0` | Deep `formToJSON` key recursion DoS | Requires invoking `formToJSON` on crafted objects. Not used. | B. Browser Runtime / SAFE PATCH |
| 25 | `GHSA-jqh4-m9w3-8hp9` | Moderate | `>=1.7.0 <1.18.0` | `1.18.0` | Fetch adapter `ReadableStream` uploads bypass `maxBodyLength` | Requires fetch upload streaming with maxBodyLength in Node. | B. Browser Runtime / SAFE PATCH |
| 26 | `GHSA-mmx7-hfxf-jppx` | Moderate | `>=1.0.0 <1.18.0` | `1.18.0` | Prototype pollution gadgets alter request construction | Requires prototype pollution gadget. None exist. | B. Browser Runtime / SAFE PATCH |
| 27 | `GHSA-7q8q-rj6j-mhjq` | Moderate | `>=1.0.0 <1.18.0` | `1.18.0` | Nested Axios option objects consume polluted prototype values | Requires prototype pollution gadget. None exist. | B. Browser Runtime / SAFE PATCH |
| 28 | `GHSA-mwf2-3pr3-8698` | Moderate | `>=1.13.0 <1.18.0` | `1.18.0` | HTTP/2 streamed uploads bypass `maxBodyLength` | Requires Node HTTP/2 streamed upload. | B. Browser Runtime / SAFE PATCH |

### 4.2 Other NPM Packages (14 Distinct Advisories)

| # | Advisory ID | Package | Installed Version | Severity | Vulnerable Range | Patched Range | Execution Category | Exploit Prerequisites & Reachability | Disposition |
|---|---|---|---|---|---|---|---|---|---|
| 29 | `GHSA-r4q5-vmmm-2653` | `follow-redirects` | `1.15.11` | Moderate | `<=1.15.11` | `1.15.12` | E. Unreachable Transitive | Node HTTP redirect follower. Stripped from Vite browser bundle. | SAFE PATCH |
| 30 | `GHSA-hmw2-7cc7-3qxx` | `form-data` | `4.0.5` | High | `>=4.0.0 <4.0.6` | `4.0.6` | E. Unreachable Transitive | Node multipart stream. Stripped from Vite browser bundle. | SAFE PATCH |
| 31 | `GHSA-28wg-ghj8-5hjv` | `nanoid` | `3.3.11` | High | `<3.3.16` | `3.3.16` | C. Build-Time | Infinite loop with negative size. PostCSS uses standard size. | SAFE PATCH |
| 32 | `GHSA-2v37-7h3g-55p8` | `nanoid` | `3.3.11` | High | `<3.3.18` | `3.3.18` | C. Build-Time | Infinite loop with zero size in custom generator. Not used. | SAFE PATCH |
| 33 | `GHSA-qx2v-qp2m-jg93` | `postcss` | `8.5.8` | Moderate | `<8.5.10` | `8.5.10` | C. Build-Time | XSS in `</style>` stringify. Compiles local CSS files only. | SAFE PATCH |
| 34 | `GHSA-6g55-p6wh-862q` | `postcss` | `8.5.8` | High | `<=8.5.11` | `8.5.12` | C. Build-Time | Path traversal via `sourceMappingURL` in CSS comments. Compiles trusted CSS. | SAFE PATCH |
| 35 | `GHSA-fxqj-rqcc-2cmp` | `postcss` | `8.5.8` | Moderate | `<=8.5.22` | `8.5.23` | C. Build-Time | Incomplete fix for GHSA-6g55-p6wh-862q. Compiles trusted CSS. | SAFE PATCH |
| 36 | `GHSA-r28c-9q8g-f849` | `postcss` | `8.5.8` | High | `<=8.5.17` | `8.5.18` | C. Build-Time | Path traversal in previous source map auto-loading. Compiles trusted CSS. | SAFE PATCH |
| 37 | `GHSA-w7jw-789q-3m8p` | `shell-quote` | `1.8.3` | Critical | `>=1.1.0 <=1.8.3` | `1.8.4` | D. CI / Dev Only | Unescaped newlines in `.op` values. Invoked only in `composer dev` CLI. | SAFE PATCH |
| 38 | `GHSA-395f-4hp3-45gv` | `shell-quote` | `1.8.3` | High | `<=1.8.4` | `1.8.5` | D. CI / Dev Only | Quadratic complexity DoS in `parse()`. Invoked only in `composer dev` CLI. | SAFE PATCH |
| 39 | `GHSA-4w7w-66w2-5vf9` | `vite` | `6.4.1` | Moderate | `<=6.4.1` | `6.4.2` | C. Build-Time / Dev Server | Path traversal in optimized deps `.map`. Production uses `vite build`. | SAFE PATCH |
| 40 | `GHSA-p9ff-h696-f583` | `vite` | `6.4.1` | High | `>=6.0.0 <=6.4.1` | `6.4.2` | C. Build-Time / Dev Server | Arbitrary file read via Vite dev server WebSocket. Dev server not exposed in production. | SAFE PATCH |
| 41 | `GHSA-v6wh-96g9-6wx3` | `vite` | `6.4.1` | Moderate | `<=6.4.2` | `6.4.3` | C. Build-Time / Dev Server | NTLMv2 hash disclosure via Windows UNC path in `launch-editor`. Production runs on Linux. | SAFE PATCH |
| 42 | `GHSA-fx2h-pf6j-xcff` | `vite` | `6.4.1` | High | `<=6.4.2` | `6.4.3` | C. Build-Time / Dev Server | `server.fs.deny` bypass on Windows alternate paths. Production runs on Linux. | SAFE PATCH |

---

## 5. Composer Lock Inventory Evidence

The table below documents the exact locked versions parsed directly from `composer.lock` for all 18 security-affected Composer packages alongside their advisory counts and highest severity:

| Package | Exact Locked Version | Advisory Count | Highest Severity |
|---|---|---|---|
| `filament/actions` | `v5.5.2` | 1 | Medium |
| `filament/filament` | `v5.5.2` | 3 | High |
| `filament/infolists` | `v5.5.2` | 1 | Medium |
| `filament/tables` | `v5.5.2` | 1 | Medium |
| `guzzlehttp/guzzle` | `7.10.0` | 9 | High |
| `guzzlehttp/psr7` | `2.9.0` | 4 | Medium |
| `laravel/framework` | `v13.5.0` | 3 | High |
| `league/commonmark` | `2.8.2` | 6 | High |
| `mtdowling/jmespath.php` | `2.8.0` | 1 | Critical |
| `phpseclib/phpseclib` | `3.0.53` | 1 | Medium |
| `symfony/html-sanitizer` | `v8.0.8` | 5 | Medium |
| `symfony/http-foundation` | `v8.0.8` | 1 | Medium |
| `symfony/http-kernel` | `v8.0.8` | 1 | High |
| `symfony/mailer` | `v8.0.8` | 1 | Medium |
| `symfony/mime` | `v8.0.8` | 2 | High |
| `symfony/polyfill-intl-idn` | `v1.36.0` | 1 | Low |
| `symfony/routing` | `v8.0.8` | 2 | Medium |
| `symfony/yaml` | `v8.0.8` | 3 | Low |

---

## 6. Composer Critical & High Reachability Analysis

Composer packages installed via `composer install --no-dev` ARE physically present in the production PHP container (`/var/www/html/vendor`). Below is the reachability analysis for all **1 Critical** and **9 High** advisories:

### 1. `mtdowling/jmespath.php` — CRITICAL (`PKSA-mnyp-475s-ywph` / `CVE-2026-54133` / `GHSA-pcw8-m77r-2528`)
- **Locked Version**: `2.8.0` (required by `aws/aws-sdk-php@3.384.2` <- `league/flysystem-aws-s3-v3@3.34.0`)
- **Affected Range**: `<2.9.1` | **Authoritative Patched Version**: `2.9.1`
- **Production Runtime Presence**: YES (`vendor/mtdowling/jmespath.php`)
- **Vulnerable Feature**: `JmesPath\CompilerRuntime` compiles unescaped function names in custom JMESPath expressions into PHP code, allowing code execution if an attacker supplies or controls the JMESPath expression and `JP_PHP_COMPILE` is enabled or `CompilerRuntime` is instantiated.
- **Repository Usage & Search Evidence**:
  - Project codebase grep search for `JmesPath`, `CompilerRuntime`, `JP_PHP_COMPILE`, and `jmespath`: **0 occurrences in application code**.
  - AWS SDK / MinIO S3 integration in [PublicStorageController.php](file:///var/www/madeena-company-profile/app/Http/Controllers/PublicStorageController.php), [CheckStorageHealth.php](file:///var/www/madeena-company-profile/app/Console/Commands/CheckStorageHealth.php), and [UploadDatabaseBackup.php](file:///var/www/madeena-company-profile/app/Console/Commands/UploadDatabaseBackup.php) interacts strictly with Laravel `Storage::disk('public')` / `Storage::disk('enterprise_backups')` file streaming operations.
  - AWS SDK uses JMESPath internally for static client waiter response evaluation where expression strings are hardcoded in the SDK definitions, not taken from user input.
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **0 credible production exploit paths identified under current repository and runtime configuration**.

### 2. `filament/filament` — HIGH (`PKSA-nsry-m1tp-jzr9` / `CVE-2026-48505` / `GHSA-mc5j-f6wx-h9qh`)
- **Locked Version**: `v5.5.2` (required by root `composer.json`)
- **Affected Range**: `>=5.0.0,<5.6.5|>=4.0.0,<4.11.5` | **Authoritative Patched Version**: `5.6.5`
- **Production Runtime Presence**: YES (`vendor/filament/filament`)
- **Vulnerable Feature**: Multi-factor authentication (app-based) recovery codes can be reused concurrently before invalidation.
- **Repository Usage & Search Evidence**:
  - [AdminPanelProvider.php](file:///var/www/madeena-company-profile/app/Providers/Filament/AdminPanelProvider.php) configures `login(SsoLogin::class)` and `profile(CustomProfile::class)`.
  - Search for `multiFactorAuthentication`: **0 occurrences**.
  - App-based MFA recovery codes are not enabled or configured in this repository.
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **feature is not enabled under current configuration**.

### 3. `guzzlehttp/guzzle` — HIGH (`PKSA-gcrk-3vtt-1r14` / `CVE-2026-69246` / `GHSA-v5mv-p594-2x33`)
- **Locked Version**: `7.10.0` (required by `laravel/framework`, `socialiteproviders/laravelpassport`, `aws/aws-sdk-php`)
- **Affected Range**: `<7.15.2` (for 7.x) | `>=8.0.0,<8.0.1` (for 8.x) | **Authoritative Patched Version**: `7.15.2` (or `8.0.1`)
- **Production Runtime Presence**: YES (`vendor/guzzlehttp/guzzle`)
- **Vulnerable Feature**: Noncanonical hostnames can bypass host-based allowlist/redirect checks.
- **Repository Execution Paths & Evidence**:
  1. **SSO / IAM Outbound HTTP**: In [SsoController.php](file:///var/www/madeena-company-profile/app/Http/Controllers/SsoController.php), outbound HTTP PATCH is sent via Laravel `Http::` using config key `config('services.laravelpassport.host')` (sourced from `env('MADEENA_IAM_URL')`). Socialite OAuth token/userinfo endpoints are similarly fixed to the configured IAM host.
  2. **AWS SDK / Flysystem S3 / MinIO Storage**: AWS SDK uses Guzzle as its HTTP transport handler for S3 API calls (PutObject, GetObject, HeadObject) configured via `config('filesystems.disks.public.endpoint')` (`env('AWS_ENDPOINT')`).
  3. **Public Storage Route ([PublicStorageController.php](file:///var/www/madeena-company-profile/app/Http/Controllers/PublicStorageController.php))**: Incoming visitor requests to `/storage/{path}` control strictly the S3 object key/path inside the bucket after traversal sanitization. The visitor CANNOT control the network request host, authority, Host header, or redirect validation logic.
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **0 credible production exploit paths identified under current repository and runtime configuration**.

### 4. `laravel/framework` — HIGH (`PKSA-3r5d-mb8f-1qw9` / `GHSA-5vg9-5847-vvmq`)
- **Locked Version**: `v13.5.0` (required by root `composer.json`)
- **Affected Range**: `>=13.0.0,<=13.9.0` (Laravel 13) | `<12.60.0` (Laravel 12) | **Authoritative Patched Version**: `13.10.0` (for Laravel 13) / `12.60.0` (for Laravel 12)
- **Production Runtime Presence**: YES (`vendor/laravel/framework`)
- **Vulnerable Feature**: CRLF injection in default `email` validation rule when email string is subsequently passed into raw mail transport headers.
- **Repository Usage & Search Evidence**: Form feedback submissions validate email (`'email' => ['nullable', 'email:rfc,dns', 'max:255']`), but Madeena does not send outgoing emails or construct raw mail headers from visitor submissions (messages are stored directly in MySQL via `GuestMessage::create`).
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **no mail sending path exists in application**.

### 5–8. `league/commonmark` — HIGH (4 Advisories: `GHSA-mh25-x5hq-wrqp`, `GHSA-jfm3-95jq-q3rf`, `GHSA-g2gp-3wwq-f4ph`, `GHSA-2q4p-g7hv-5rgv` / `CVE-2026-71488`)
- **Locked Version**: `2.8.2` (required by `laravel/framework`, `filament/forms`)
- **Affected Range**: `<2.9.0` | **Authoritative Patched Version**: `2.9.0`
- **Production Runtime Presence**: YES (`vendor/league/commonmark`)
- **Vulnerable Feature**: Denial of service (ReDoS / quadratic time / collision loops) when parsing maliciously crafted Markdown inputs with duplicate footnotes, colliding slugs, or adjacent inline attribute blocks.
- **Repository Usage & Search Evidence**: Public visitors cannot submit Markdown to server-side CommonMark parsers. Research articles use structured Tiptap JSON (`content_json`), static pages use Filament Builder JSON, and public event feedback messages are plain text strings stored and escaped without Markdown conversion.
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **no untrusted Markdown parsing path exists**.

### 9. `symfony/http-kernel` — HIGH (`PKSA-dw7n-x7f5-zf63` / `CVE-2026-45075` / `GHSA-6439-2f28-8p8q`)
- **Locked Version**: `v8.0.8` (required by `laravel/framework`)
- **Affected Range**: `>=7.4.0,<7.4.12|>=8.0.0,<8.0.12` | **Authoritative Patched Version**: `8.0.12`
- **Production Runtime Presence**: YES (`vendor/symfony/http-kernel`)
- **Vulnerable Feature**: HEAD request method filter bypass on Symfony controller attributes (`#[IsGranted(..., methods: ['GET'])]`, `#[IsSignatureValid]`, `#[IsCsrfTokenValid]`).
- **Repository Usage & Search Evidence**:
  - Project code search for `IsGranted`, `IsSignatureValid`, `IsCsrfTokenValid`: **0 occurrences**.
  - Madeena uses standard Laravel routing, middleware (`VerifyCsrfToken`, `Authenticate`), and Laravel policy gates.
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **vulnerable Symfony controller attributes are absent from project**.

### 10. `symfony/mime` — HIGH (`PKSA-2n2k-66v2-bwg3` / `CVE-2026-45067` / `GHSA-qpmx-3rfj-7rhv`)
- **Locked Version**: `v8.0.8` (required by `laravel/framework`, `symfony/mailer`)
- **Affected Range**: `>=8.0.0,<8.0.12` | **Authoritative Patched Version**: `8.0.12`
- **Production Runtime Presence**: YES (`vendor/symfony/mime`)
- **Vulnerable Feature**: Email Header / SMTP Command Injection via CRLF in `Symfony\Component\Mime\Address`.
- **Repository Usage & Search Evidence**: Madeena does not instantiate `Symfony\Component\Mime\Address` with user-supplied data or transmit outgoing emails based on visitor submissions.
- **Exploit Prerequisites Present**: **NO**.
- **Reachability Conclusion**: Installed and vulnerable by version; **no mail sending path exists in application**.

---

## 7. Composer Medium & Low Advisories Summary (36 Advisories across 12 Packages)

All remaining Composer advisories represent locked upstream vendor dependencies in `composer.lock`:

- `filament/actions` (1 Medium: `PKSA-ndkp-2znf-9m7c` / `CVE-2026-48067`, patched: `5.6.4`) — Inconsistent scope on AttachAction/AssociateAction select fields.
- `filament/filament` (2 Medium: `PKSA-317j-243v-z7tc` / `CVE-2026-48500` [temp file upload on auth pages], `PKSA-3rh1-zh9g-4mq5` / `CVE-2026-48166` [timing enumeration], patched: `5.6.5`).
- `filament/infolists` (1 Medium: `PKSA-jm9c-w1fc-4m3p` / `CVE-2026-48167`, patched: `5.6.5`) — ImageEntry unvalidated values.
- `filament/tables` (1 Medium: `PKSA-qx8b-yc1b-44yc` / `CVE-2026-48167`, patched: `5.6.5`) — ImageColumn unvalidated values.
- `guzzlehttp/guzzle` (8 Medium: `PKSA-64n8-y7ph-742t`, `PKSA-255d-jhgf-n9f6`, `PKSA-79j7-g8n3-9v43`, `PKSA-34h8-n574-x6x3`, `PKSA-57r8-m31b-q5p6`, `PKSA-p98h-crf5-k35r`, `PKSA-g3c5-926w-4rjh`, `PKSA-8x1f-82q6-4jvv`, patched: `7.15.2`).
- `guzzlehttp/psr7` (4 Medium: `PKSA-42x8-67rw-8p48`, `PKSA-6c2g-m137-97d8`, `PKSA-rc32-6p75-5r79`, `PKSA-yxf5-v22q-d7s8`, patched: `2.9.2`).
- `laravel/framework` (1 Medium: `PKSA-66v8-h2wf-832f` / `CVE-2026-47000`, 1 Untyped: `PKSA-8j75-17n5-x667`, patched: `13.9.1`).
- `league/commonmark` (2 Medium: `PKSA-w937-ffh6-n5f1`, `PKSA-cfk1-h77m-8167`, patched: `2.9.0`).
- `phpseclib/phpseclib` (1 Medium: `PKSA-432p-hv1d-chf7` / `CVE-2026-55599`, patched: `3.0.54`).
- `symfony/html-sanitizer` (4 Medium, 1 Low: `PKSA-3d8r-4bff-vcj1`, `PKSA-bvdf-tk8n-sbsf`, `PKSA-jwvg-gphd-brbz`, `PKSA-4fc7-y875-17k3`, `PKSA-q2wy-m7mz-kg58`, patched: `8.0.13`).
- `symfony/http-foundation` (1 Medium: `PKSA-y6py-qpv1-h52p` / `CVE-2026-48736`, patched: `8.0.13`).
- `symfony/mailer` (1 Medium: `PKSA-28rh-rzzn-djk4` / `CVE-2026-45068`, patched: `8.0.12`).
- `symfony/mime` (1 Medium: `PKSA-wtxr-p26d-nn42` / `CVE-2026-45070`, patched: `8.0.12`).
- `symfony/polyfill-intl-idn` (1 Low: `PKSA-dwsq-ppd2-mb1x` / `CVE-2026-46644`, patched: `1.38.1`).
- `symfony/routing` (2 Medium: `PKSA-bf7t-jnpz-492k` / `CVE-2026-48784`, `PKSA-yc7t-91v9-99xs` / `CVE-2026-45065`, patched: `8.0.13`).
- `symfony/yaml` (3 Low: `PKSA-v5yj-8nmz-sk2q` / `CVE-2026-45304`, `PKSA-ft77-7h5f-p3r6` / `CVE-2026-45305`, `PKSA-b14r-zh1d-vdrc` / `CVE-2026-45133`, patched: `8.0.12`).

---

## 8. Install-Script & Supply-Chain Security Observation

During `npm ci`, npm inspects package install scripts. The repository's package lock contains one package with an active postinstall script:

- **Package**: `esbuild` (`0.25.12`, direct devDependency of `vite`)
- **Script**: `"postinstall": "node install.js"`
- **Purpose**: Verifies and links the platform-specific native binary (`@esbuild/linux-x64`, `@esbuild/darwin-arm64`, etc.) required by the esbuild bundler.
- **Execution Context**: Runs during `npm ci` in `node-builder` stage and CI.
- **Supply-Chain Assessment**: esbuild's postinstall is the package's standard platform-binary setup mechanism. No specific malicious behavior was identified from the package metadata reviewed in this audit; no independent network-trace or full upstream source audit was performed.
- **Status in #10A**: Documented only; no script permissions modified.

---

## 9. Candidate Remediation Matrices (for Task #10B)

### 9.1 Composer Production Dependencies Remediation Matrix

| Package | Locked Version | Primary Advisory | Authoritative Patched Version | Direct / Transitive | Production Runtime? | Current Reachability | Semver / Constraint Impact | Proposed #10B Action |
|---|---|---|---|---|---|---|---|---|
| `mtdowling/jmespath.php` | `2.8.0` | `GHSA-pcw8-m77r-2528` (Critical) | `^2.9.1` | Transitive (`aws/aws-sdk-php`) | Yes | None (no user JMESPath input) | Minor/Patch within AWS SDK constraints | Update lockfile in #10B |
| `filament/filament` | `v5.5.2` | `GHSA-mc5j-f6wx-h9qh` (High) | `^5.6.5` | Direct (`^5.0`) | Yes | None (MFA recovery codes not enabled) | Minor within `^5.0` | Update in #10B |
| `guzzlehttp/guzzle` | `7.10.0` | `GHSA-v5mv-p594-2x33` (High) | `^7.15.2` | Transitive (`laravel/framework`) | Yes | None (static IAM/S3 endpoints only) | Minor within `^7.0` | Update lockfile in #10B |
| `laravel/framework` | `v13.5.0` | `GHSA-5vg9-5847-vvmq` (High) | `^13.10.0` | Direct (`^13.0`) | Yes | None (no outgoing email transport) | Minor within `^13.0` | Update in #10B |
| `league/commonmark` | `2.8.2` | `GHSA-2q4p-g7hv-5rgv` (High) | `^2.9.0` | Transitive (`laravel/framework`) | Yes | None (no untrusted Markdown input) | Minor within `^2.0` | Update lockfile in #10B |
| `symfony/http-kernel` | `v8.0.8` | `GHSA-6439-2f28-8p8q` (High) | `^8.0.12` | Transitive (`laravel/framework`) | Yes | None (Symfony attributes not used) | Patch within `^8.0` | Update lockfile in #10B |
| `symfony/mime` | `v8.0.8` | `GHSA-qpmx-3rfj-7rhv` (High) | `^8.0.12` | Transitive (`laravel/framework`) | Yes | None (no outgoing email transport) | Patch within `^8.0` | Update lockfile in #10B |

### 9.2 Node / NPM Dependencies Remediation Matrix

| Package | Current Version | Candidate Fixed Version | Direct / Transitive | Semver Impact | Expected Lockfile Impact | Expected Build Risk | Proposed #10B Action |
|---|---|---|---|---|---|---|---|
| `axios` | `1.13.6` | `^1.18.0` | Direct (`devDependencies`) | Minor (`^1.7.4` -> `^1.18.0`) | `axios`, `follow-redirects`, `form-data` | Minimal (XHR/fetch API unchanged) | Update `package.json` & lockfile in #10B |
| `concurrently` | `9.2.1` | `^9.2.2` | Direct (`devDependencies`) | Patch / Minor | `concurrently`, `shell-quote` | None (CLI tool only) | Update in #10B |
| `follow-redirects` | `1.15.11` | `1.15.12` | Transitive (`axios`) | Patch | Transitive lockfile update | None | Resolved via `axios` update |
| `form-data` | `4.0.5` | `4.0.6` | Transitive (`axios`) | Patch | Transitive lockfile update | None | Resolved via `axios` update |
| `nanoid` | `3.3.11` | `3.3.18` | Transitive (`postcss`) | Patch | Transitive lockfile update | None | Resolved via `vite`/`postcss` update |
| `postcss` | `8.5.8` | `8.5.23` | Transitive (`vite`) | Patch | Transitive lockfile update | None | Resolved via `vite` update |
| `shell-quote` | `1.8.3` | `1.8.5` | Transitive (`concurrently`) | Patch | Transitive lockfile update | None | Resolved via `concurrently` update |
| `vite` | `6.4.1` | `^6.4.3` | Direct (`devDependencies`) | Patch (`^6.0.11` semver) | `vite`, `postcss`, `nanoid` | Minimal (standard bugfix patch) | Update lockfile in #10B |

---

## 10. Verification & Build Reproducibility Evidence

The current lockfile and build pipeline were fully reproduced and verified locally:

1. **`npm ci`**:
   - Result: 103 packages added in 4s, exit code `0`.
   - Reported: 8 vulnerable package entries (42 distinct advisory IDs).
2. **`npm run build`**:
   - Result: 59 modules transformed, built in 2.10s, exit code `0`.
   - Generated assets: CSS, JS, KaTeX fonts in `public/build/`.
3. **Pint Ratchet (`./scripts/pint-ratchet.sh HEAD`)**:
   - Result: 1 post-baseline file checked (`tests/Feature/ReleaseSmokeTest.php`), 0 violations, PASS.
4. **PHPUnit Test Suite (`vendor/bin/phpunit`)**:
   - Result: 162 tests, 996 assertions, 100% OK (Runtime: 15.466s).
