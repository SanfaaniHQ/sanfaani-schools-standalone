# Standalone Vs SaaS Vs Marketplace

| Product path | Main user | Main flow | Billing/signup posture |
| --- | --- | --- | --- |
| Standalone School Edition | One private school | Installer, school admin, license activation, local school dashboard | SaaS signup and SaaS billing are not the main standalone flow |
| SaaS Platform | Sanfaani platform operator and many schools | Hosted multi-school onboarding, subscriptions, demo and lead flow | SaaS billing and customer acquisition can be primary |
| Marketplace Package | Technical buyer or reseller | Package review, installation checklist, buyer docs | Package builder is supporting infrastructure, not the main standalone school workflow |

Standalone is a private installation. The school can run locally without internet when the local server and database are available. The local database is the source of truth.

SaaS mode is for centralized hosted operations across many schools. It can expose school signup, subscriptions, demo requests, billing, and customer acquisition tools.

Marketplace packaging is a delivery and sales channel. It helps technical buyers or implementation partners install the product, but the standalone school user should mainly see installer, license activation, and the school dashboard.

Done-for-you service can bridge the gap for non-technical schools. Sanfaani or a partner can install, configure, activate, and hand over the standalone system without asking the school owner to manage packaging details.

## Standalone Surface Boundary

Standalone mode is treated as a private single-school portal, not the public SaaS, marketplace, demo, or customer acquisition product. In `single_school` deployment mode, the main product surface should prioritize the Laravel portal: installation status, license status, school profile, branding, backups, updates, system health, sync/offline status, students, staff, classes, subjects, sessions, terms, admissions, results, and CBT.

The application keeps SaaS, marketplace, demo, and marketing code in the repository because those boundaries are still useful for other product paths and future packaging. Do not delete those modules aggressively just to make the standalone UI quieter. Hide, gate, rename, or demote them through deployment behavior, feature flags, and standalone surface gates until the product boundary is stable.

By default, standalone surface gates hide SaaS billing/subscription links, marketplace demo links, public demo request flows, platform marketing/customer acquisition tools, and multi-school platform administration from the main standalone user experience. Direct routes for clearly non-standalone areas should return 404 unless an explicit configuration flag re-enables the surface for a controlled internal purpose.

The Laravel portal remains the school operations source of truth. The Next.js website is separate public website/add-on work; it is not required for the standalone school portal and should not be described as the source of truth for operations.
