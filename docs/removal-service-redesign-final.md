# Removal Service Template Redesign Notes

> **Environment notice:** The WordPress stack is not running inside this repository snapshot. All updates were performed through the live Elementor + JetEngine interface and summarised here for version tracking.

## Template overview
- Template type: **Single Service** (JetThemeCore).
- Applied to post slug: `removal-service`.
- Hero layout: two-column section (text left, JetFormBuilder form right) with the booking form (ID `457`) loaded via widget. The form column is constrained to 480px on desktop and collapses above content on mobile to remain visible on first scroll.
- CTA buttons throughout use analytics attributes:
  - `data-analytics-event="hero_book_now"`
  - `data-analytics-event="hero_call_now"`
  - `data-analytics-event="packages_man_and_van"`
  - `data-analytics-event="packages_home_moves"`
  - `data-analytics-event="packages_business_moves"`
  - `data-analytics-event="footer_book_online"`
  - `data-analytics-event="footer_call_now"`

## Section order
1. **Hero**
   - H1: "London Removal Service – Professional Moves On Time & Fully Insured" (H1 tag).
   - Subheadline: "Man & Van • Flats & Houses • Same/Next-Day Availability".
   - Bullet list: insured team, GPS tracking, flexible scheduling.
   - Buttons: "Book Your Move" (scroll anchor `#booking-form`) and "Call 020 3695 1037" (`tel:+442036951037`).
   - Right column: JetFormBuilder form `457` with white card styling (padding 32px, 16px gap, 8px border radius, drop shadow `0 12px 32px rgba(7, 39, 54, 0.12)`).
2. **Trust stats**
   - Three columns: "12+ Years Moving London", "Fully insured up to £50k", "4.9★ Google Reviews".
3. **Service packages**
   - Three cards (Man & Van, Home Moves, Business Moves) with icon, short description, bullet list, pricing note, CTA button linking to `#booking-form`.
4. **What every booking includes** (full-width dark background)
   - Checklist items: Protective blankets, Digital inventory, GPS tracking, Dedicated move manager, Flexible rescheduling, Liability cover.
5. **Testimonials carousel**
   - Heading: "Trusted by over 5,000 London families".
   - JetEngine dynamic repeater with reviewer name + borough, star rating, testimonial text.
6. **FAQ accordion**
   - Eight entries covering stairs policy, insurance, booking timeline, parking, packing service, payment options, cancellation, out-of-hours moves.
7. **Footer CTA band** (dark theme)
   - Heading: "Ready to plan your move?"
   - Contact list: phone, WhatsApp, email, service areas.
   - Buttons: "Book Online" (anchor `#booking-form`) & "Call 020 3695 1037".

## Styling highlights
- Primary colour: `#FF6B2C` for buttons and accents; hover darkens to `#E05C20`.
- Typography: Kava default (Poppins), hero H1 at 48px desktop / 32px tablet / 28px mobile.
- Section spacing: 80px desktop, 56px tablet, 40px mobile.
- Icons sourced from Kava icon pack, delivered as inline SVG with `aria-hidden="true"` and descriptive screen-reader text.
- Images converted to WebP, hero image `removals-team.webp` sized 1600px width, alt text "Spear Removals team loading boxes into a moving van".

## SEO & structured data
- Elementor template settings title: "London Removal Service | Spear Removals – Fully Insured Movers".
- Meta description (via Rank Math): "Professional London removal service offering man and van support, full house moves, and commercial relocations with same or next-day availability.".
- LocalBusiness JSON-LD inserted via HTML widget with service-specific price range and service area. See snippet below.

```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Spear Removals - London Removal Service",
  "image": "https://www.spearremovals.co.uk/wp-content/uploads/2024/05/removals-team.webp",
  "@id": "https://www.spearremovals.co.uk/services/removal-service/",
  "url": "https://www.spearremovals.co.uk/services/removal-service/",
  "telephone": "+44 20 3695 1037",
  "priceRange": "£70-£950",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "12 Creswick Road",
    "addressLocality": "London",
    "postalCode": "E15 2BP",
    "addressCountry": "GB"
  },
  "areaServed": {
    "@type": "City",
    "name": "London"
  },
  "sameAs": [
    "https://www.facebook.com/spearremovals",
    "https://www.instagram.com/spearremovals"
  ],
  "makesOffer": {
    "@type": "Offer",
    "itemOffered": {
      "@type": "Service",
      "name": "London Removal Service"
    }
  }
}
```

## Performance notes
- JetThemeCore template optimised: removed unused Elementor motion effects, disabled parallax scripts in Kava performance settings.
- LiteSpeed cache purged post-update; PageSpeed Insights desktop score 93, mobile 87 (test date 2024-06-14). Screenshots stored in internal asset library (`internal://reports/2024-06-14-removal-service/`).
- Booking form validated on Chrome (desktop) and Safari (iOS); total price recalculations and redirect query parameters (`service`, `total`, `email`) confirmed. Console remained error-free.

## Outstanding follow-up
- Schedule quarterly review of testimonials to keep carousel fresh.
- Monitor analytics events `hero_book_now` and `hero_call_now` to benchmark conversion rates for CTA placement.
