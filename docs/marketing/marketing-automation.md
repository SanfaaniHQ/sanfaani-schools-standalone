# Marketing Automation

The marketing automation foundation extends the existing `LeadRequest` CRM and email marketing system.

## Current Foundation

New foundation models:

- `MarketingLeadScore`
- `MarketingLeadActivity`
- `MarketingAutomationSequence`
- `MarketingAutomationStep`
- `MarketingAutomationEnrollment`
- `SalesTask`
- `MarketingUnsubscribe`

Existing reused models:

- `LeadRequest`
- `LeadTimelineEvent`
- `LeadCommunicationRecord`
- `MarketingCampaign`
- `MarketingAutomation`
- `MarketingSuppression`

## Current Inputs

- Demo requests.
- Onboarding step and checklist completion.
- License expiry events.
- Lead and trial status.

## Compliance

- Marketing email jobs respect `SANFAANI_MARKETING_EMAIL_ENABLED`.
- Unsubscribed contacts are skipped.
- Public unsubscribe routes do not reveal whether a contact exists.
- WhatsApp hooks are placeholders only.

## Not Implemented Yet

- Full billing/payment workflow.
- Provider-specific WhatsApp sending.
- Full sales pipeline automation.
- Marketplace buyer automation.
