# Changelog

All notable changes to `laravel-pdf` will be documented in this file.

## 2.1.1 - 2026-02-10

### What's Changed

* Add support for basic auth in the Gotenberg driver by @grantholle in https://github.com/spatie/laravel-pdf/pull/303

### New Contributors

* @grantholle made their first contribution in https://github.com/spatie/laravel-pdf/pull/303

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/2.1.0...2.1.1

## 2.1.0 - 2026-02-09

### What's Changed

* Add Gotenberg driver by @freekmurze in https://github.com/spatie/laravel-pdf/pull/302

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/2.0.0...2.1.0

## 2.0.0 - 2026-02-08

### What's new

v2 introduces a **driver-based architecture**, allowing you to choose between different PDF generation backends.

#### New drivers

- **Cloudflare** - Generate PDFs using Cloudflare's Browser Rendering API
- **DomPdf** - Generate PDFs using DomPdf (no external binary required)
- **Browsershot** - Remains the default driver

#### New features

- Queued PDF generation via `saveQueued()`
- PDF metadata support (title, author, subject, keywords, creator, creation date)
- Runtime driver switching with `->driver('dompdf')`
- Custom driver support

#### Breaking changes

- `spatie/browsershot` must now be explicitly required via Composer
- `getBrowsershot()` has been removed — use `withBrowsershot()` instead
- Config file structure has changed (new `driver` key)
- Laravel 10 support has been dropped

See the full [upgrade guide](https://github.com/spatie/laravel-pdf/blob/main/UPGRADING.md) for migration steps.

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.9.0...2.0.0

## 1.9.0 - 2026-01-31

### What's Changed

* Fix typo in alternatives.md regarding Chromium by @lienriviere in https://github.com/spatie/laravel-pdf/pull/277
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/293
* Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/290
* Bump actions/setup-node from 5 to 6 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/284
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/283
* Add PHP 8.4 to test matrix, add PR trigger by @freekbot in https://github.com/spatie/laravel-pdf/pull/296
* Fix defaults not being preserved in queue:work by @freekmurze in https://github.com/spatie/laravel-pdf/pull/298

### New Contributors

* @lienriviere made their first contribution in https://github.com/spatie/laravel-pdf/pull/277
* @freekbot made their first contribution in https://github.com/spatie/laravel-pdf/pull/296
* @freekmurze made their first contribution in https://github.com/spatie/laravel-pdf/pull/298

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.8.0...1.9.0

## 1.8.0 - 2025-09-12

### What's Changed

* Update config publish tag in configuration.md by @acip in https://github.com/spatie/laravel-pdf/pull/273
* Update issue template by @AlexVanderbist in https://github.com/spatie/laravel-pdf/pull/274
* feat: add global 'no sandbox' option by @EvanSchleret in https://github.com/spatie/laravel-pdf/pull/276
* Bump actions/setup-node from 4 to 5 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/275

### New Contributors

* @acip made their first contribution in https://github.com/spatie/laravel-pdf/pull/273
* @AlexVanderbist made their first contribution in https://github.com/spatie/laravel-pdf/pull/274
* @EvanSchleret made their first contribution in https://github.com/spatie/laravel-pdf/pull/276

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.7.1...1.8.0

## 1.7.1 - 2025-08-29

### What's Changed

* fix: merges the config file. by @joekaram in https://github.com/spatie/laravel-pdf/pull/272

### New Contributors

* @joekaram made their first contribution in https://github.com/spatie/laravel-pdf/pull/272

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.7.0...1.7.1

## 1.7.0 - 2025-08-25

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/267
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/262
* docs: add example using withBrowsershot to disable web security and allow local file access. by @nick322 in https://github.com/spatie/laravel-pdf/pull/268
* feat: add configuration support for browsershot binary paths by @A909M in https://github.com/spatie/laravel-pdf/pull/270
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/spatie/laravel-pdf/pull/271

### New Contributors

* @nick322 made their first contribution in https://github.com/spatie/laravel-pdf/pull/268

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.6.0...1.7.0

## 1.6.0 - 2025-06-16

### What's Changed

* feat: Add Macroable trait to PdfBuilder class by @A909M in https://github.com/spatie/laravel-pdf/pull/261

### New Contributors

* @A909M made their first contribution in https://github.com/spatie/laravel-pdf/pull/261

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.6...1.6.0

## 1.5.6 - 2025-06-11

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot in https://github.com/spatie/laravel-pdf/pull/244
* Update alternatives.md by @mininoz in https://github.com/spatie/laravel-pdf/pull/251
* Fix failing tests by @sertxudev in https://github.com/spatie/laravel-pdf/pull/256
* Ensure PDF is saved before asserting its content by @sertxudev in https://github.com/spatie/laravel-pdf/pull/258

### New Contributors

* @mininoz made their first contribution in https://github.com/spatie/laravel-pdf/pull/251
* @sertxudev made their first contribution in https://github.com/spatie/laravel-pdf/pull/256

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.5...1.5.6

## 1.5.5 - 2025-02-20

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/spatie/laravel-pdf/pull/218
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/spatie/laravel-pdf/pull/223
* Laravel 12.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-pdf/pull/228

### New Contributors

* @laravel-shift made their first contribution in https://github.com/spatie/laravel-pdf/pull/228

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.4...1.5.5

## 1.5.4 - 2025-01-06

### What's Changed

* improve @InlinedImage to detect mime type by @Seb33300 in https://github.com/spatie/laravel-pdf/pull/180

### New Contributors

* @Seb33300 made their first contribution in https://github.com/spatie/laravel-pdf/pull/180

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.3...1.5.4

## 1.5.3 - 2024-12-16

### What's Changed

* update contributing link as no CONTRIBUTING.md file by @Nathan-Cowin in https://github.com/spatie/laravel-pdf/pull/192
* Complete views and disks by @adelf in https://github.com/spatie/laravel-pdf/pull/200
* Allow spatie/browsershot v5 by @hailwood in https://github.com/spatie/laravel-pdf/pull/199
* Fix inconsistent behavior in save() method when using vs not using a disk by @aalyusuf in https://github.com/spatie/laravel-pdf/pull/196
* add assertDontSee test method by @Nathan-Cowin in https://github.com/spatie/laravel-pdf/pull/191

### New Contributors

* @Nathan-Cowin made their first contribution in https://github.com/spatie/laravel-pdf/pull/192
* @adelf made their first contribution in https://github.com/spatie/laravel-pdf/pull/200
* @hailwood made their first contribution in https://github.com/spatie/laravel-pdf/pull/199
* @aalyusuf made their first contribution in https://github.com/spatie/laravel-pdf/pull/196

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.2...1.5.3

## 1.5.2 - 2024-07-16

### What's Changed

* Bump dependabot/fetch-metadata from 2.1.0 to 2.2.0 by @dependabot in https://github.com/spatie/laravel-pdf/pull/158
* Make getBrowsershot public by @dbpolito in https://github.com/spatie/laravel-pdf/pull/161

### New Contributors

* @dbpolito made their first contribution in https://github.com/spatie/laravel-pdf/pull/161

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.1...1.5.2

## 1.5.1 - 2024-05-08

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.3.1 to 2.4 by @dependabot in https://github.com/spatie/laravel-pdf/pull/123
* Fix typo in view path by @PrestaEdit in https://github.com/spatie/laravel-pdf/pull/128
* Bump dependabot/fetch-metadata from 2.0.0 to 2.1.0 by @dependabot in https://github.com/spatie/laravel-pdf/pull/131
* Fix setting the name via download() by @stevethomas in https://github.com/spatie/laravel-pdf/pull/139

### New Contributors

* @PrestaEdit made their first contribution in https://github.com/spatie/laravel-pdf/pull/128
* @stevethomas made their first contribution in https://github.com/spatie/laravel-pdf/pull/139

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.5.0...1.5.1

## 1.5.0 - 2024-04-08

### What's Changed

* PDFBuilder now honours pdf name set by either name() or download() methods during download by @albertStaalburger in https://github.com/spatie/laravel-pdf/pull/114
* Bump dependabot/fetch-metadata from 1.6.0 to 2.0.0 by @dependabot in https://github.com/spatie/laravel-pdf/pull/109

### New Contributors

* @albertStaalburger made their first contribution in https://github.com/spatie/laravel-pdf/pull/114

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.4.0...1.5.0

## 1.4.0 - 2024-03-04

### What's Changed

* fix method and class names in paper format docs by @vintagesucks in https://github.com/spatie/laravel-pdf/pull/94
* fix default paper format in docs by @vintagesucks in https://github.com/spatie/laravel-pdf/pull/96

### New Contributors

* @vintagesucks made their first contribution in https://github.com/spatie/laravel-pdf/pull/94

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.3.0...1.4.0

## 1.3.0 - 2024-03-01

### What's Changed

* adding a fix to support variables as params by @ArielMejiaDev in https://github.com/spatie/laravel-pdf/pull/85
* Add support for visibility by @msucevan in https://github.com/spatie/laravel-pdf/pull/92

### New Contributors

* @msucevan made their first contribution in https://github.com/spatie/laravel-pdf/pull/92

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.2.0...1.3.0

## 1.2.0 - 2024-02-12

### What's Changed

* add InlinedImage Blade directive by @ArielMejiaDev in https://github.com/spatie/laravel-pdf/pull/79

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.1.3...1.2.0

## 1.1.3 - 2024-02-06

### What's Changed

* Update testing-pdfs.md by @RVP04 in https://github.com/spatie/laravel-pdf/pull/47
* Fix heading hierarchy in `formatting-pdfs.md` by @austincarpenter in https://github.com/spatie/laravel-pdf/pull/52
* Update testing-pdfs.md by @RVP04 in https://github.com/spatie/laravel-pdf/pull/49
* Fix issue about puppeteer by @NeftaliYagua in https://github.com/spatie/laravel-pdf/pull/61
* chore(docs): fix link to sidecar-browsershot package by @greatislander in https://github.com/spatie/laravel-pdf/pull/62
* Add printColor directive by @ArielMejiaDev in https://github.com/spatie/laravel-pdf/pull/60
* Modify break statement in FakePdfBuilder. by @Lintume in https://github.com/spatie/laravel-pdf/pull/77
* typo fix footer end tag by @rakibhoossain in https://github.com/spatie/laravel-pdf/pull/76

### New Contributors

* @RVP04 made their first contribution in https://github.com/spatie/laravel-pdf/pull/47
* @austincarpenter made their first contribution in https://github.com/spatie/laravel-pdf/pull/52
* @NeftaliYagua made their first contribution in https://github.com/spatie/laravel-pdf/pull/61
* @greatislander made their first contribution in https://github.com/spatie/laravel-pdf/pull/62
* @ArielMejiaDev made their first contribution in https://github.com/spatie/laravel-pdf/pull/60
* @Lintume made their first contribution in https://github.com/spatie/laravel-pdf/pull/77
* @rakibhoossain made their first contribution in https://github.com/spatie/laravel-pdf/pull/76

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.1.2...1.1.3

## 1.1.2 - 2024-01-16

- fix download assertion docs

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.1.0...1.1.2

## 1.1.1 - 2024-01-16

- fix required PHP version

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.1.0...1.1.1

## 1.1.0 - 2024-01-14

### What's Changed

* Add ->paperSize()-method by @jeffreyvanhees in https://github.com/spatie/laravel-pdf/pull/33

### New Contributors

* @jeffreyvanhees made their first contribution in https://github.com/spatie/laravel-pdf/pull/33

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.0.1...1.1.0

## 1.0.1 - 2024-01-08

### What's Changed

* Added showBackground() by default into getBrowsershot() function.  by @bawbanksy in https://github.com/spatie/laravel-pdf/pull/24
* Fix nitpicks by @freekmurze

### New Contributors

* @bawbanksy made their first contribution in https://github.com/spatie/laravel-pdf/pull/24

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/1.0.0...1.0.1

## 1.0.0 - 2024-01-02

- initial release

## 0.0.5 - 2024-01-01

### What's Changed

* Bump aglipanci/laravel-pint-action from 2.3.0 to 2.3.1 by @dependabot in https://github.com/spatie/laravel-pdf/pull/4

### New Contributors

* @dependabot made their first contribution in https://github.com/spatie/laravel-pdf/pull/4

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/0.0.4...0.0.5

## 0.0.4 - 2023-12-29

### What's Changed

* Workbench Improvements by @crynobone in https://github.com/spatie/laravel-pdf/pull/1

### New Contributors

* @crynobone made their first contribution in https://github.com/spatie/laravel-pdf/pull/1

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/0.0.3...0.0.4

## 0.0.3 - 2023-12-28

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/0.0.2...0.0.3

## 0.0.2 - 2023-12-27

**Full Changelog**: https://github.com/spatie/laravel-pdf/compare/0.0.1...0.0.2

## 0.0.1 - 2023-12-26

- experimental release
