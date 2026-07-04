# School SMTP And Platform Fallback

School SMTP is configured in the application. An authorised administrator opens **Email Delivery**, enters the school's outgoing mail details, and uses **Test School SMTP** before saving. School administrators do not need to edit production `.env` mail values.

The SMTP password is encrypted with Laravel's application encryption before it is stored. The real password is never returned to the browser. A blank password field or the displayed mask keeps the existing password; entering a new value replaces it. If `APP_KEY` changes and an old password cannot be decrypted, the page marks it **Needs re-entry** and the administrator must enter and save it again.

## School SMTP fields

- Enable school SMTP override.
- Mailer: SMTP.
- Host: the provider's exact outgoing SMTP hostname, without `https://` or a path.
- Port: normally 465 or 587.
- Username: normally the full mailbox address.
- Password: mailbox password or provider-issued App Password.
- Encryption: SSL for implicit TLS on port 465; TLS for STARTTLS on port 587.
- From Address: normally the authenticated mailbox or an authorised alias.
- From Name and optional Reply-To Email.
- Optional connection timeout.

Certificate verification remains enabled. If the certificate does not match a friendly mail alias, use the exact server hostname supplied by the provider.

## Gmail And Google Workspace

Use `smtp.gmail.com` with either:

- port 465 and SSL; or
- port 587 and TLS.

Use the full Gmail or Google Workspace address as the username. Accounts using two-step verification generally require a Google App Password; a normal account password may be rejected. The From Address should match the authenticated account or an alias authorised by Google. Do not use obsolete “less secure app” instructions.

## cPanel And Webmail

Open cPanel/Webmail **Connect Devices** or the mail-client configuration page and copy the exact outgoing server hostname. Use the full mailbox address and mailbox password. Providers commonly offer 465 + SSL and 587 + TLS. The From Address should normally match the mailbox.

Do not assume the hostname is `mail.example.com`; it may be a server hostname. A TLS hostname mismatch usually means the exact cPanel outgoing server hostname is required.

## Testing And Status

**Test School SMTP** uses the values currently visible in the form, including unsaved changes. A blank password uses the saved encrypted password when it is still usable. Temporary values are not saved. A successful result means the selected SMTP server accepted the message for delivery; it does not guarantee inbox placement.

**Test Platform Fallback** is a separate action. It never changes the school test result. If the fallback driver is `log` or `array`, the UI reports that no external message was delivered.

The status panel distinguishes enabled, configured, password availability, last school test result, fallback policy, and fallback transport. A result produced from unsaved form values is labeled **temporary values** so it is not mistaken for a successful test of the saved configuration. “Enabled” alone does not mean SMTP is working.

## Platform fallback

Installation-level Laravel mail values may remain in `.env` as the optional platform fallback. They are not required for a school SMTP override. Transactional mail tries valid enabled school SMTP first and uses the platform fallback only when policy permits. A successful school send is not sent again through fallback.

After changing platform `.env` values, rebuild configuration cache. Changing school SMTP in the UI does not require clearing Laravel caches.

## Troubleshooting

- Authentication failed: confirm the full username and mailbox/App Password.
- Connection failed or timed out: confirm hostname, port, outbound SMTP access, and firewall policy.
- TLS failed: confirm 465 + SSL or 587 + TLS and use the certificate's exact server hostname.
- Sender rejected or relay denied: use the authenticated mailbox or an authorised alias.
- Accepted but not in inbox: check spam/promotions, provider activity, suppression lists, and recipient spelling.
- Poor inbox placement: verify SPF, DKIM, and DMARC for the sending domain.
- Password needs re-entry: save the current SMTP password again; do not change `APP_KEY` merely to repair mail.

Review `storage/logs` privately. Mail diagnostics include school ID, host, port, encryption, transport, exception class, and a safe category; they must not include SMTP passwords, encrypted secrets, message bodies, `APP_KEY`, or database credentials.

For a safe command-line status check, run `php artisan standalone:mail-diagnose`. It does not connect or send by default. Add `--school=ID` to select a school and add an explicit `--recipient=user@example.com` only when a synchronous saved-school-SMTP test is intended. The command never prints passwords or encrypted values.
