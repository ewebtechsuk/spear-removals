# Removal Service Booking Form Audit

This repository snapshot does not contain a running WordPress instance, so the checks below document what could be confirmed by source review and the adjustments made to keep the JetFormBuilder setup consistent with the **Removal Service** requirements.

## Form embedding (Phase 1.1–1.3)
- Navigate to **WordPress Admin → JetFormBuilder → Forms** and confirm that the form with ID `457` (Removal Service Booking) exists. The repository does not hold the block markup, so confirmation must happen in the dashboard.
- In **Services → Removal Service**, open the Elementor/Block editor and verify that either the JetFormBuilder block or the shortcode `[jet_form_builder id="457"]` is embedded in the page template.
- Spot-check front-end assets in the browser dev tools. No JavaScript or asset loading errors are visible from the code audit, but the live site should still be observed for 404s or console exceptions during interactions.

## Field wiring and live pricing (Phase 1.4–1.6)
- Hidden fields `service_id` and `base_price` are expected to use the macros `%current_id%` and `%META::base_price%` respectively. The `booking-orders-validation.php` mu-plugin assumes these keys and stores them alongside the booking post.
- Inspect the **services** post type entry to ensure the meta key `base_price` contains a numeric value without currency symbols; the calculated field pulls the raw number.
- The calculated field `total_price` should have **Recalculate on change** enabled. Formula:
  ```
  %META::base_price% + %van_size% + SUM(%extras%) + (%distance_km% * 1.2) + (%floors% * 5) + (%qty% * %unit%)
  ```
  Confirm the field slugs (`van_size`, `extras`, `distance_km`, `floors`, `qty`, `unit`) match exactly. If the legacy slug `num_floors` remains anywhere, update the form field to `floors`; the mu-plugin keeps backwards compatibility but the live calculation only responds to the active slug.

## Post-submit actions (Phase 1.7)
- Within JetFormBuilder actions, validate the **Insert appointment** mapping: Service ID should point to `%service_id%`, and the Date/Time fields should reference the datetime controls used on the form. Ensure Customer Name, Email, and Phone use the relevant text inputs.
- For the **Send Email** action, verify the recipient `info@spearremovals.co.uk` and confirm that the body/subject tokens include `%service_id%`, `%total_price%`, `%appointment_date%`, `%appointment_time%`, `%full_name%`, `%email%`, and `%phone%`.
- Confirm the **Redirect to Page** action uses `/checkout/?service=%service_id%&total=%total_price%&email=%email%`. Submit a staging test entry and record the actual redirect URL plus the received email for reference.

## Caching (Phase 1.8)
- No caching exclusions are defined in source. Use the hosting control panel (e.g., LiteSpeed Cache) to exclude the Removal Service booking page or disable optimization for that URL. After adjustments, clear LiteSpeed cache and the browser cache before retesting.

## Follow-up verification (Phase 1.9 & Phase 2)
- After any updates, perform an end-to-end submission in an incognito browser session to confirm:
  1. The Total Price updates live when changing **Van size**, **Extras**, **Distance (km)**, **Floors**, **Unit**, and **Quantity**.
  2. Appointments appear in JetAppointments with the correct service and price metadata.
  3. Emails and redirects include the expected placeholders and values.
- Capture screenshots of dev tools console/network tabs and the Total Price widget if issues persist. Store them alongside this audit for future troubleshooting.
