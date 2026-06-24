=== Cookie Consent by Osano ===
Contributors: akilez
Tags: cookies, privacy, gdpr, consent
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reusable cookie consent plugin powered by Osano CookieConsent.

== Description ==

Cookie Consent by Osano provides a configurable cookie consent banner for WordPress sites using the Osano CookieConsent library.

Current features:

* Settings screen under `Settings > Cookie Consent`
* Tabbed settings layout with in-page help panels
* Configurable consent mode, banner position, and theme
* Configurable banner message and button text
* Configurable cookie name, domain, path, and expiry
* Optional ipapi-based location services for EU-only banner display
* Configurable location lookup timeout and cache duration
* Built-in Google Analytics 4 integration with consent-gated loading
* Consent-aware deferred script loading for admin-managed custom analytics or marketing snippets
* Shortcodes for reopening cookie settings and displaying CookieConsent attribution
* Banner and shortcode color controls with additional CSS
* Custom browser events for consent initialization and status changes
* WordPress filters and actions for site-specific overrides
* Activation, deactivation, and uninstall lifecycle hooks
* Lightweight functional tests runnable with `php tests/run.php`
* GitHub-hosted plugin updates through vendored Plugin Update Checker

== Installation ==

1. Upload the plugin zip through `Plugins > Add New Plugin`.
2. Activate the plugin.
3. Go to `Settings > Cookie Consent`.
4. Configure the banner settings for the site.

== Frequently Asked Questions ==

= Does this plugin include the CookieConsent library? =

Yes. This package includes the vendored Osano CookieConsent v3 assets used by the plugin.

= Can the plugin show the banner only for EU visitors? =

Yes. When location services are enabled, the plugin uses ipapi to detect the visitor country in the browser and only initializes the banner for EU visitors.

= Can themes or custom code react to consent changes? =

Yes. The plugin dispatches custom browser events and exposes WordPress hooks for site-specific integrations.

= Can this plugin block Google Analytics 4 until consent is granted? =

Yes. The plugin now includes a built-in GA4 integration that loads through the consent gate when you enable it and provide a measurement ID.

= Can I block other analytics or marketing scripts? =

Yes. Add script URLs or inline script contents in `Settings > Cookie Consent > Integrations`. Scripts added there load only when the current consent mode allows tracking. Scripts hardcoded directly by another theme or plugin still need to be removed or moved into this gate.

= How can visitors reopen cookie settings? =

Enable `Display Cookie Settings Tab` to show the built-in floating tab, or add `[ccbo_cookie_consent_reopen]` anywhere shortcodes are supported. The shortcode accepts optional `text` and `class` attributes.

= Is there an attribution shortcode? =

Yes. Add `[ccbo_cookie_consent_attribution]` where you want to display optional CookieConsent attribution.

= How do I run the tests? =

From the plugin root, run `php tests/run.php`. The test suite uses a lightweight WordPress shim and does not require Composer or PHPUnit.

= How do plugin updates work? =

This plugin is configured to read updates from GitHub releases. Publish a new tagged release in `https://github.com/Akilez/Osano-CookieConsent-WP-Plugin` with the packaged `cookie-consent-by-osano.zip` asset and WordPress will detect it through the bundled update checker.

== Changelog ==

= 0.3.2 =

* Added `[ccbo_cookie_consent_reopen]` for placing a cookie settings button anywhere shortcodes are supported
* Added `[ccbo_cookie_consent_attribution]` for optional CookieConsent attribution
* Renamed the revokable setting label to `Display Cookie Settings Tab`
* Added `window.ccboCookieConsent.reopen()` for custom frontend controls
* Added styling controls for the reopen button and attribution shortcode

= 0.3.1 =

* Added admin-managed script gate entries for analytics and marketing scripts

= 0.3.0 =

* Added consent-gated deferred script loading for custom analytics and marketing integrations
* Added a built-in Google Analytics 4 integration with consent-aware loading
* Added frontend consent helper methods for checking status and tracking eligibility
* Fixed non-EU banner skips so consent-gated scripts can still load when EU targeting is bypassed
* Updated documentation for EU lookup fallback and consent-gated integrations

= 0.2.2 =

* Fixed malformed Windows-built package issues by standardizing release ZIP generation in GitHub Actions
* Removed UTF-8 BOM output that caused activation warnings on some installs
* Corrected GitHub updater repository configuration and release workflow behavior

= 0.2.1 =

* Reserved for the unreleased tag alignment fix

= 0.2.0 =

* Added GitHub-based WordPress plugin updates using Plugin Update Checker
* Added `Update URI` header for external update protection
* Added GitHub Actions workflow to build and attach the release ZIP
* Added updater documentation and third-party credit attribution
* Added lightweight functional tests for settings, frontend config, and plugin lifecycle behavior

= 0.1.0 =

* Initial scaffold
* Added admin settings page
* Added frontend config generation and JS initialization
* Vendored Osano CookieConsent v3 assets
* Added activation, deactivation, and uninstall lifecycle hooks
* Added tabbed settings UI and contextual help panels
* Added ipapi-based location services for EU-only banner display
