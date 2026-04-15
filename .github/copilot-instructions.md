# Project: tomflidr.cz

Personal portfolio and presentation website of **Tom Flídr** — freelance PHP/JS/.NET
developer based in Melčany, CZ.

Live site: https://tomflidr.cz  
Source: https://github.com/tomFlidr/tomflidr.cz  
License: BSD 3-Clause (`LICENSE.md`)

---

## Tech stack

| Layer | Technology |
|---|---|
| PHP runtime | PHP >= 8.2, OOP, PHP 8 Attributes annotations |
| PHP framework | **MvcCore 5.3.x** (author's own open-source framework) |
| PHP templates | `.phtml` (native PHP) + **Latte 3.1** (for XML-based page content) |
| Cache | **Redis** via `mvccore/ext-cache-redis` |
| Config | `.ini` files with cached variant (`mvccore/ext-config-cached`) |
| Routing | `mvccore/ext-router-media-localization` (desktop/mobile + en/de/cs) |
| Translations | CSV files via `mvccore/ext-translator-csv` |
| CSP | Dynamic Content Security Policy via `mvccore/ext-tool-csp` |
| Debug | Tracy (Nette) + MvcCore Tracy extensions |
| Frontend language | **TypeScript 5.x**, compiled to ES5, no bundler (plain `tsc`) |
| Frontend i18n | `intl-messageformat` (IIFE build, no module loader) |
| CSS | Plain CSS, no preprocessor; bundled and minified by MvcCore Assets helper |
| Web server | Apache, `mod_rewrite`, `.htaccess` |
| Page content | XML files validated against `Var/Document.xsd`, parsed into Document model |
| Package manager | Composer (PHP) + npm (TS/JS only for build tooling) |
| Dev tooling | PHPMetrics 2.x, Visual Studio (`.sln` + `.phpproj`) |

---

## Repository layout

```
tomflidr.cz/
├── App/                        PHP application (PSR-4 namespace App\)
│   ├── Bootstrap.php           Application init (MvcCore, Redis, router, env)
│   ├── config.ini              Production config
│   ├── config.dev.ini          Development config
│   ├── env.ini                 Environment detection (dev IPs, gamma IP)
│   ├── Controllers/
│   │   ├── Base.php            Base controller (CSP, translator, locale setup)
│   │   ├── Front.php           Front controller base (navigation, breadcrumbs)
│   │   ├── Common/
│   │   │   └── Assets.php      CSS/JS bundle and theme management
│   │   └── Fronts/
│   │       ├── Index.php       Homepage, 404, /status
│   │       ├── Sitemap.php     /sitemap.xml
│   │       ├── Training.php    Training page
│   │       └── Indexes/
│   │           ├── Contacts.php
│   │           ├── Cv.php
│   │           ├── Prices.php
│   │           ├── Projects.php
│   │           ├── References.php
│   │           └── Services.php
│   ├── Models/
│   │   ├── Contact.php         Contact data (phone, email, social, IČO, IBAN, PGP…)
│   │   ├── Price.php           Hourly rates (EUR/CZK)
│   │   ├── Training.php        Training stats (150 courses since 2008)
│   │   ├── SitemapUrl.php
│   │   ├── Navigations/        Navigation item/set/breadcrumb models
│   │   ├── Prices/
│   │   │   └── Rates.php
│   │   └── Xml/
│   │       ├── Entity.php      Base XML entity
│   │       ├── Schema.php      XSD validation
│   │       └── Entities/
│   │           ├── Document.php  Page content model (route filters, Latte rendering)
│   │           └── Svg.php       SVG logotype model with resize support
│   ├── Routers/
│   │   └── MediaAndLocalization.php  Router (cached routes, media+locale)
│   ├── Tools/
│   │   ├── Cli.php
│   │   └── Git.php             Reads git HEAD commit hash for /status
│   ├── Views/
│   │   ├── Helpers/            Latte/phtml view helpers (translate, hr, nl2br, xml)
│   │   ├── Layouts/            Main HTML layout (standard.phtml + partials)
│   │   └── Scripts/fronts/     Page view scripts (index, cv, contacts, prices…)
│   └── Cli/                    CLI entry, WinFork tool, background process helpers
│
├── Var/
│   ├── Document.xsd            XML schema for page content files
│   ├── Documents/              Page content in XML, one file per page per locale
│   │   ├── cs.xml / cs/        Czech: homepage, cv, kontakty, sluzby, ceny, reference…
│   │   ├── de.xml / de/        German equivalents
│   │   └── en.xml / en/        English equivalents
│   ├── Translations/
│   │   ├── cs_CZ.csv
│   │   ├── de_DE.csv
│   │   └── en_GB.csv
│   ├── Logs/
│   ├── Tmp/
│   └── info(at)tomflidr.cz.public  PGP public key
│
└── www/                        Apache document root
    ├── index.php               Entry point
    ├── .htaccess
    └── static/
        ├── css/                Themes: dark/static, dark/animated, light
        │   ├── all/            Global + theme styles
        │   ├── pages/          Per-page styles
        │   └── print/
        ├── js/
        │   ├── build/          Compiled TypeScript output
        │   └── libs/           intl-messageformat.iife.js, reflect-lite.js
        ├── ts/                 TypeScript sources
        │   ├── src/
        │   │   ├── Core/       Environment, Layout, MediaSiteVersion, Page, Translator
        │   │   └── Front/      Navigations, Page (front-specific logic)
        │   ├── tsconfig.json
        │   └── package.json
        ├── img/
        └── fonts/
```

---

## Routing and localisation

- Router: `App\Routers\MediaAndLocalization` extends `MvcCore\Ext\Routers\MediaAndLocalization`
- Media versions: `full` (desktop, URL prefix `""`) and `mobile` (URL prefix `m`)
- Localisations: `en-GB` (default), `de-DE`, `cs-CZ`
- Equivalents: `en-US/en-CA/en-AU → en-GB`, `de-AT → de-DE`, `sk-SK → cs-CZ`
- Routes are cached in Redis under key `routes` with tag `router`
- Universal `document` route maps any path to `Fronts\Index:Index` and uses
  `Document::RouteFilterIn` / `RouteFilterOut` to resolve the XML content file

---

## Page content model

Each page is an XML file in `Var/Documents/{locale}/` validated against `Var/Document.xsd`.
The `Document` model parses the XML, Latte renders dynamic parts embedded in XML nodes.
Documents serve as the single source of truth for page text, structure and metadata.

---

## Environments

Defined in `App/env.ini`:

| Name | Detection |
|---|---|
| `development` | Client IP `127.0.0.1` or `::1`, hostname `tomflidr.czx` |
| `gamma` | Client IP `109.105.39.4` (Tracy bar on production server) |
| `production` | everything else |

In development, `config.dev.ini` is loaded instead of `config.ini`.
Cache can be disabled via `app.cache = 0` in config.

---

## Themes

Three CSS themes switchable at runtime (stored in session):

- `dark/static` (default)
- `dark/animated`
- `light`

Theme is applied as a CSS class/bundle selection in `Controllers\Common\Assets`.

---

## Frontend (TypeScript)

- Source: `www/static/ts/src/`
- Build: `npm run build` (`tsc` only, no bundler)
- Output: `www/static/js/build/`
- Target: ES5, module `none` (concatenated output via `outFile`)
- Core modules: `Environment`, `Layout`, `MediaSiteVersion`, `Page`, `Translator`
- Front modules: `Navigations`, `Page` (front-specific overrides)
- Runtime i18n: `intl-messageformat` IIFE loaded as a plain script

---

## Key configuration values (`App/config.ini`)

```ini
app.name              = "Tom Flídr"
app.timezoneDefault   = Europe/Prague
app.cache             = 1
app.themes.default    = dark/static
app.session.identity  = 2592000   ; 30 days
app.hostVerify.baseHost = tomflidr.cz
footer.sourceLink     = https://github.com/tomFlidr/tomflidr.cz/
assets.cssMinify/jsMinify/cssJoin/jsJoin = 1
```

Secrets (`google.analyticsId`, `google.mapsApiKey`) are injected at deploy time
as `%placeholder%` values — never commit real keys.

---

## Composer dependencies (production)

```
mvccore/mvccore                        ^5.3.14   Core framework
mvccore/ext-cache-redis                ^5.3.0    Redis cache
mvccore/ext-config-cached              ^5.3.1    Cached INI config
mvccore/ext-router-media               ^5.3.2    Media version routing
mvccore/ext-router-media-localization  ^5.3.1    Localised routing
mvccore/ext-translator-csv             ^5.3.1    CSV translations
mvccore/ext-tool-locale                ^5.3.0    Locale helper
mvccore/ext-tool-csp                   ^5.3.2    Content Security Policy
mvccore/ext-tool-cli-winfork           ^5.3.0    Windows CLI fork helper
mvccore/ext-tool-collections           ^5.3.0    Collection helpers
mvccore/ext-view-helper-formatmoney    ^5.3.0    Money formatting
mvccore/ext-view-helper-assets         ^5.3.5    CSS/JS asset bundling
mvccore/ext-debug-tracy                ^5.3.8    Tracy integration
mvccore/ext-debug-tracy-mvccore        ^5.3.3    Tracy MvcCore panel
mvccore/ext-debug-tracy-routing        ^5.3.3    Tracy routing panel
mvccore/ext-debug-tracy-session        ^5.3.3    Tracy session panel
latte/latte                            ^3.1.3    Template engine
```

Dev: `phpmetrics/phpmetrics ^2.8`

---

## Code style conventions

- Language: PHP 8.2+, TypeScript 5.x, plain CSS, XML/Latte
- Indentation: **tabs** everywhere (PHP, TS, CSS, XML)
- Comments: **English only**
- PHP style: PSR-4 autoloading, OOP, PHP 8 Attributes, no unnecessary dependencies
- No CSS preprocessor, no JS bundler beyond `tsc`
- Secrets never in VCS — use `%placeholder%` in `.ini` files

---

### File encoding
All files in this project use **UTF-8 without BOM**.
When using PowerShell to read or search files, always add `-Encoding UTF8`.

### Editing files
When editing files **always** use the `replace_string_in_file` or `multi_replace_string_in_file` tool.
Never use terminal scripts (PowerShell, Node.js) to **write or modify** C# files — prefer file-editing tools.
Exception: files outside the project (`.editorconfig`, `.md`) may be written via `node -e` with a single-line command using `fs.writeFileSync`.
If you need PowerShell only for **reading or searching**, use it, but add `-Encoding UTF8` and run the command as a single line, not as a block script.
Line endings in PHP files are **LF** – do not fix them manually, `replace_string_in_file` will preserve them.

### Updating the instruction file after completing a task
After every successfully completed task, you MUST update this file (`.github/copilot-instructions.md`) to reflect the current state of the application.
I'll always tell you when it's done. This file is the primary source of context for future AI sessions – keep it accurate and up to date.

At the same time, check whether the changes affects anything other described in `README.md`. 
If so, also update `README.md` to reflect the current state of the application.
