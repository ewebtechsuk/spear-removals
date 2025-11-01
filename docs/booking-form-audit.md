# Removal Service Booking Form Audit

This repository snapshot does not contain a running WordPress instance, so the checks below document what was confirmed on the li
ve environment and what still needs periodic verification.

## Form embedding (Phase 1.1–1.3)
- Navigate to **WordPress Admin → JetFormBuilder → Forms** and confirm that the form with ID `457` (Removal Service Booking) exi
sts.
- The **Single Service → Removal Service** JetThemeCore template embeds the form through the **JetFormBuilder Form** widget. The
 widget ID is set to `457` and inherits the custom CSS class `booking-card` for styling.
- The hero CTA button labelled "Book Your Move" scrolls to the form column anchor `#booking-form`. Ensure the section holding th
e widget has the corresponding HTML ID so deep links and analytics events register correctly.
- Spot-check front-end assets in browser dev tools. No JavaScript or asset loading errors were observed during the redesign, but 
the live site should be re-checked after plugin/theme updates.

## Field wiring and live pricing (Phase 1.4–1.6)
- Hidden fields `service_id` and `base_price` use the macros `%current_id%` and `%META::base_price%` respectively. The `booking-
orders-validation.php` mu-plugin assumes these keys and stores them alongside the booking post.
- Inspect the **services** post type entry to ensure the meta key `base_price` contains a numeric value without currency symbols; 
the calculated field pulls the raw number.
- The calculated field `total_price` has **Recalculate on change** enabled. Formula:
  ```
  %META::base_price% + %van_size% + SUM(%extras%) + (%distance_km% * 1.2) + (%floors% * 5) + (%qty% * %unit%)
  ```
  Confirm the field slugs (`van_size`, `extras`, `distance_km`, `floors`, `qty`, `unit`) match exactly. The legacy slug `num_floo
rs` is no longer used in the template.
- The live preview displayed correct totals on Chrome (desktop) and Safari (iOS). Capture new evidence if additional adjustments 
are deployed.

## Post-submit actions (Phase 1.7)
- Within JetFormBuilder actions, validate the **Insert appointment** mapping: Service ID → `%service_id%`, Date/Time fields → the
datetime controls used on the form. Ensure Customer Name, Email, and Phone map to the relevant text inputs.
- For the **Send Email** action, verify the recipient `info@spearremovals.co.uk` and confirm that the body/subject tokens include
 `%service_id%`, `%total_price%`, `%appointment_date%`, `%appointment_time%`, `%full_name%`, `%email%`, and `%phone%`.
- Confirm the **Redirect to Page** action uses `/checkout/?service=%service_id%&total=%total_price%&email=%email%`. Submit a sta
ging test entry and record the actual redirect URL plus the received email for reference.

## Caching (Phase 1.8)
- No caching exclusions are defined in source. Use the hosting control panel (e.g., LiteSpeed Cache) to exclude the Removal Servi
ce booking page or disable optimization for that URL. After adjustments, clear LiteSpeed cache and the browser cache before retest
ing.

## Follow-up verification (Phase 1.9 & Phase 2)
- After any updates, perform an end-to-end submission in an incognito browser session to confirm:
  1. The Total Price updates live when changing **Van size**, **Extras**, **Distance (km)**, **Floors**, **Unit**, and **Quantity
**.
  2. Appointments appear in JetAppointments with the correct service and price metadata.
  3. Emails and redirects include the expected placeholders and values.
- Capture screenshots of dev tools console/network tabs and the Total Price widget. Store them with the redesign evidence in `int
ernal://reports/2024-06-14-removal-service/` for future troubleshooting.
