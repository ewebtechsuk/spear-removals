# Removal Service Task Tracker

This tracker extracts every actionable follow-up documented across:

- `docs/removal-service-redesign-final.md`
- `docs/booking-form-audit.md`
- `docs/template-exports/removal-service-template-export.md`

Use the **Open tasks** tables for work planning. The **Validated references** section records what has already been checked off in prior redesign rounds and can be used as acceptance criteria during regression.

## Open tasks

### Form & flow
| Status | Task | Source |
| --- | --- | --- |
| [ ] | Schedule periodic post-update end-to-end submission tests (incognito) to reconfirm live pricing, JetAppointments entries, and outbound email/redirect placeholders. | `booking-form-audit.md` (Follow-up verification) |
| [ ] | Maintain LiteSpeed cache exclusions or purge routines so the booking form page is never served stale after edits. | `booking-form-audit.md` (Caching) |

### Performance & responsive
| Status | Task | Source |
| --- | --- | --- |
| [ ] | Re-run performance and accessibility checks whenever plugins/themes update or new assets are introduced; log PageSpeed scores and console status. | `removal-service-redesign-final.md` (Performance notes) |

### Documentation & assets
| Status | Task | Source |
| --- | --- | --- |
| [ ] | Export refreshed Elementor template JSONs (`removal-service-template-YYYY-MM-DD.json`) after layout changes and store them under `internal://exports/elementor/`. | `removal-service-template-export.md` |
| [ ] | Capture supporting screenshots/dev tools evidence for future regressions in `internal://reports/2024-06-14-removal-service/` (or successor folders). | `booking-form-audit.md` (Follow-up verification) |

### Review & monitoring
| Status | Task | Source |
| --- | --- | --- |
| [ ] | Schedule quarterly testimonial carousel refresh to keep content current. | `removal-service-redesign-final.md` (Outstanding follow-up) |
| [ ] | Monitor analytics events `hero_book_now` and `hero_call_now` to benchmark CTA performance after updates. | `removal-service-redesign-final.md` (Outstanding follow-up) |
| [ ] | Share major redesign iterations with stakeholders for approval prior to publishing live changes. | `removal-service-redesign-final.md` (Outstanding follow-up) |
| [ ] | Track booking conversions post-launch (hero CTAs, form submissions, bounce rate) and log insights in the ops log. | `removal-service-redesign-final.md` (Outstanding follow-up) |

## Validated references

These items have been confirmed as of the 2024-06-14/15 redesign cycle and remain the acceptance baseline:

- Template scope locked to the `removal-service` slug via JetThemeCore conditions.
- Hero layout configured as two columns with JetFormBuilder form 457 styled as a `booking-card` and visible above the fold across breakpoints.
- CTA copy, analytics attributes, and hero benefits list match the redesign specification.
- Trust stats, service packages, inclusions section, testimonials carousel, FAQ accordion, and footer CTA band all reflect the documented content and structure.
- JetFormBuilder hidden fields (`service_id`, `base_price`) and calculated field (`total_price`) wiring validated; submission redirect/email/JetAppointments logging confirmed.
- SEO title/meta description and LocalBusiness JSON-LD widget set per spec.
- Responsive tuning ensures booking card max-width 480px on desktop, correct stacking order on mobile, and CTA analytics attributes intact.
- PageSpeed Insights benchmark (≥93 desktop / ≥87 mobile) achieved post-cache purge with clean console logs.
- Documentation repositories updated with redesign decisions, audit evidence, and template export paths.
