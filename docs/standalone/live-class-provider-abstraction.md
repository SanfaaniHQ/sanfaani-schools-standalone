# Live Class Provider Abstraction

Stage 17 adds a provider abstraction layer on top of the manual live-class foundation.

## Purpose

The abstraction gives live classes a provider contract, provider registry, provider capability metadata, and provider-aware validation/display without making Sanfaani Schools dependent on Google Meet, Zoom, or Microsoft Teams APIs.

Manual meeting links remain the only active production behavior.

## Active Provider

- Manual link is enabled.
- Manual meeting URLs are required.
- Recording URLs remain optional and must be valid `http` or `https` URLs when present.
- Meeting passwords remain optional and are visible only to authorized school users.
- No provider credentials are required for the manual provider.

## Future Provider Metadata

The registry can describe future providers such as Google Meet, Zoom, and Microsoft Teams, but those providers are disabled for API automation.

Capability metadata records whether a provider supports:

- manual links;
- auto-created rooms;
- recording links;
- meeting passwords;
- credentials.

For Stage 17, auto-created rooms are not supported by any provider.

## Security Boundaries

Stage 17 does not add:

- Google Meet API integration;
- Zoom API integration;
- Microsoft Teams API integration;
- OAuth;
- provider credential storage;
- token refresh;
- webhooks;
- generated meeting rooms;
- recording import or sync;
- live-class attendance tracking;
- offline live class.

Audit metadata stores the provider key only. Meeting passwords, OAuth tokens, provider secrets, provider payloads, and private invite data must not be written to audit logs.

## Permissions

Provider metadata does not change access rules.

- School Admins can manage live classes inside their current school.
- Teachers can manage only assigned class and subject scopes.
- Accountants and Result Officers do not gain live-class management access.
- Student and Parent live-class visibility remains deferred.
- Cross-school live class and LMS links remain blocked.

## Operational Note

Live classes require internet. Schools must create the room in their external provider manually, paste the meeting link into Sanfaani Schools, and optionally add the recording link later.
