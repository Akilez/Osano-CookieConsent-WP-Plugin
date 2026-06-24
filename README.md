# Cookie Consent by Osano

A reusable WordPress plugin for adding GDPR-style cookie consent notices across multiple WordPress sites.

## Current Status

This plugin now includes:

- a valid WordPress plugin bootstrap
- a small class-based plugin structure
- a tabbed WordPress admin settings page under `Settings > Cookie Consent`
- a right-hand quick-guide panel for each settings tab
- saved plugin options using the WordPress Settings API
- sanitization and default values for core consent settings
- vendored Osano CookieConsent v3 JS and CSS assets
- ipapi-based location services for EU-only banner display
- frontend config generation from saved admin settings
- browser events for consent initialization, status changes, and location lookups
- consent-gated deferred script loading for admin-managed and site-specific analytics and marketing integrations
- lightweight PHP functional tests that run without Composer or a WordPress test install
- vendored GitHub update support via Plugin Update Checker
- a GitHub Actions workflow that builds the release ZIP asset for tagged releases
- WordPress filters and actions for site-specific overrides

## Structure

```text
cookie-consent-by-osano/
  AGENTS.md
  CREDITS.md
  README.md
  readme.txt
  index.php
  uninstall.php
  cookie-consent-by-osano.php
  includes/
    class-plugin.php
    class-assets.php
    class-settings.php
    class-updater.php
    index.php
  assets/
    index.php
    css/
      admin-settings.css
      ccbo-cookie-consent.css
      cookieconsent.min.css
      index.php
    js/
      admin-settings.js
      ccbo-cookie-consent.js
      cookieconsent.min.js
      index.php
  vendor/
    plugin-update-checker/
  tests/
    bootstrap.php
    TestCase.php
    AssetsTest.php
    PluginLifecycleTest.php
    SettingsTest.php
    run.php
  .github/
    workflows/
      release.yml
```

## Admin Settings

The current settings screen includes these tabs:

- `General Settings`
- `Banner Content`
- `Cookie Settings`
- `Location Services`
- `Integrations`
- `Styling`

Key configuration areas include:

- enable or disable banner output
- consent mode, banner position, theme, and revokable behavior
- message, button labels, and policy URL
- cookie name, domain, path, and expiration
- ipapi endpoint, timeout, and location cache duration
- built-in Google Analytics 4 consent-gated loading
- admin-managed analytics and marketing script entries that load through the consent gate
- banner color controls and plain additional CSS

All settings are stored under the option key `ccbo_cookie_consent_options`.

## Location Services

When `Location services` is enabled in `General Settings`, the plugin uses `ipapi` in the browser to determine the visitor country before CookieConsent initializes.

Behavior:

- if the visitor is detected in the EU, the banner initializes normally
- if the visitor is outside the EU, the banner is skipped
- if the lookup fails, times out, or returns invalid data, the plugin falls back to showing the banner so EU visitors are not skipped because of a network failure
- visitor location results are cached in the browser for the configured number of hours

The plugin passes the resolved country into CookieConsent via `law.countryCode`.

## Frontend Behavior

When enabled, the plugin:

- registers and enqueues the vendor and plugin CSS/JS files
- passes the saved config into JavaScript through `window.ccboCookieConsentConfig`
- optionally resolves the visitor country via ipapi before initialization
- initializes CookieConsent when appropriate
- dispatches `ccboCookieConsentInitialised`
- dispatches `ccboCookieConsentChanged`
- dispatches `ccboCookieConsentUnavailable`
- dispatches `ccboCookieConsentLocationResolved`
- dispatches `ccboCookieConsentLocationError`
- dispatches `ccboCookieConsentSkipped`

The plugin also exposes these WordPress extension points:

- `apply_filters( 'ccbo_cookie_consent_enabled', $enabled )`
- `apply_filters( 'ccbo_cookie_consent_config', $config )`
- `apply_filters( 'ccbo_cookie_consent_default_options', $defaults )`
- `apply_filters( 'ccbo_cookie_consent_deferred_scripts', $scripts )`
- `apply_filters( 'ccbo_cookie_consent_policy_url', $url )`
- `do_action( 'ccbo_cookie_consent_before_banner_init' )`
- `do_action( 'ccbo_cookie_consent_after_banner_init' )`

## Consent-Gated Scripts

The plugin can now enforce consent-aware loading for scripts added in `Settings > Cookie Consent > Integrations` or registered through the `ccbo_cookie_consent_deferred_scripts` filter.

It also includes a built-in Google Analytics 4 integration that uses the same consent gate when a measurement ID is configured in the admin.

Admin script gate entries support:

- label
- enabled or disabled state
- analytics or marketing category
- external script URL
- inline script contents
- async and defer attributes

Behavior:

- in `opt-in` mode, deferred scripts do not load until the visitor explicitly allows cookies
- in `opt-out` mode, deferred scripts load by default and stop loading for future page views after the visitor declines
- in `info` mode, deferred scripts load normally because the banner is informational only
- when EU-only targeting skips the banner for a non-EU visitor, deferred scripts still load
- when location lookup fails, the banner still shows and deferred scripts remain gated by the active consent mode

Example registration:

```php
add_filter( 'ccbo_cookie_consent_deferred_scripts', function ( $scripts ) {
	$scripts[] = array(
		'id'  => 'google_analytics',
		'src' => 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX',
		'attributes' => array(
			'async' => true,
		),
	);

	$scripts[] = array(
		'id'     => 'google_analytics_init',
		'inline' => "window.dataLayer = window.dataLayer || [];\nfunction gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', 'G-XXXXXXXXXX');",
	);

	return $scripts;
} );
```

Frontend helper:

- `window.ccboCookieConsent.getStatus()`
- `window.ccboCookieConsent.hasAnswered()`
- `window.ccboCookieConsent.allowsTracking()`
- `window.ccboCookieConsent.loadDeferredScripts()`

Browser events now include consent state that site code can react to:

- `ccboCookieConsentInitialised`
- `ccboCookieConsentChanged`
- `ccboCookieConsentDeferredScriptLoaded`

Important limitation:

- this only controls scripts added to the plugin script gate or registered through the plugin hook
- if a theme or another plugin hardcodes analytics, pixels, or tag manager snippets directly into the page, those scripts must be moved behind this gate or removed from their original source

## GitHub Updates

The plugin is configured to update from GitHub releases instead of WordPress.org.

Implementation details:

- the plugin header includes `Update URI: https://github.com/Akilez/Osano-CookieConsent-WP-Plugin`
- the vendored `plugin-update-checker` library handles update discovery
- GitHub release assets are enabled and filtered to the packaged `cookie-consent-by-osano.zip` asset
- a GitHub token can be supplied later with the `CCBO_COOKIE_CONSENT_GITHUB_TOKEN` constant or the `ccbo_cookie_consent_update_token` filter if the repository becomes private

Relevant update filters:

- `apply_filters( 'ccbo_cookie_consent_update_repository_url', $repository_url )`
- `apply_filters( 'ccbo_cookie_consent_update_branch', $branch )`
- `apply_filters( 'ccbo_cookie_consent_update_asset_pattern', $regex )`
- `apply_filters( 'ccbo_cookie_consent_update_token', $token )`

## Release Process

To ship a new version through GitHub updates:

1. Bump the plugin version in `cookie-consent-by-osano.php` and update the changelog/docs.
2. Commit and push the changes.
3. Create and push a Git tag such as `v0.3.1`.
4. Let GitHub Actions run `.github/workflows/release.yml`.
5. Confirm the workflow attached `cookie-consent-by-osano.zip` to the GitHub release.
6. WordPress sites using the plugin will detect the newer release through the updater library.

The packaged ZIP excludes local-only project files such as `AGENTS.md`, `.github/`, `tests/`, and temp packaging artifacts.

## Tests

Run the lightweight functional suite from the repo root:

```powershell
php tests\run.php
```

What it currently covers:

- default option values
- option sanitization and validation behavior
- frontend config generation
- deferred script normalization
- admin script gate sanitization and frontend config output
- policy URL filtering
- custom CSS trimming
- activation default seeding and merge behavior
- uninstall cleanup

This suite uses a tiny WordPress shim in `tests/bootstrap.php`, so it does not require Composer, PHPUnit, or a full WordPress test environment.

## Plugin Lifecycle

The plugin includes:

- `register_activation_hook()` to initialize default options
- `register_deactivation_hook()` placeholder for future cleanup tasks
- `register_uninstall_hook()` and `uninstall.php` to remove the saved plugin option on uninstall

## Credits

See [CREDITS.md](CREDITS.md) for third-party credits and source attributions.

## Next Steps

1. Add browser-level or WordPress integration tests that verify admin-managed deferred scripts stay blocked until consent allows them.
