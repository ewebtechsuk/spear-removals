# Removal Service Booking Form Audit

This repository snapshot does not contain a running WordPress instance, so the checks below document what could be confirmed by source review and the adjustments made to keep the JetFormBuilder setup consistent with the **Removal Service** requirements.

## Form embedding (Phase 1.1–1.3)
- Source control only includes theme and plugin files; page shortcodes must be reviewed within WordPress admin. No issues were observable from the codebase.
- No JavaScript or asset loading errors surfaced during the repository audit. Runtime checks should still be executed in a browser session.

## Field wiring and live pricing (Phase 1.4–1.6)
- The mu-plugin `booking-orders-validation.php` sanitises submission payloads. It now accepts both `floors` and legacy `num_floors` field names, preventing mismatches that stopped the Total Price field from reacting to the **Floors** selector. Hidden fields `service_id` and `base_price` remain unchanged in code and should continue to resolve via JetFormBuilder macros.
- JetFormBuilder’s calculated field formula must reference `%floors%`. With the updated plugin logic, either slug (`floors` or `num_floors`) is normalised before being stored.

## Post-submit actions (Phase 1.7)
- Email, appointment creation, and redirect rules live entirely in the WordPress database. Review these within JetFormBuilder to ensure service and pricing tokens line up with the adjusted field slug.

## Caching (Phase 1.8)
- No caching exclusions are defined in source. Confirm page-level cache bypass within the WordPress dashboard or hosting panel.

## Follow-up verification (Phase 1.9 & Phase 2)
- After deploying these changes, perform an end-to-end submission in the browser to confirm:
  1. The Total Price updates when **Floors** is changed.
  2. Emails and appointments receive the updated `floors` value.
  3. Redirect URLs include the expected query parameters.

Capture screenshots of any front-end errors if they reappear so the configuration can be rechecked against this baseline.
