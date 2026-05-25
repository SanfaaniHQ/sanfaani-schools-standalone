# Docs Platform Recommendation

## Options

| Platform | Strengths | Tradeoffs |
| --- | --- | --- |
| Mintlify | Fast hosted docs, polished navigation, strong search, simple Markdown/MDX authoring. | Hosted workflow and pricing dependency. |
| Docusaurus | Mature open-source static docs, versioning, plugins, easy self-hosting. | More engineering setup and design work. |
| Nextra | Excellent Next.js/MDX experience, good for teams already using Next.js. | More custom infrastructure and ownership. |
| GitBook | Friendly editor and collaboration flow. | Less code-native and can become harder to review through PRs. |

## Recommendation

Use **Docusaurus** for `doc.sanfaani.net`.

Reasons:

- The documentation is repo-first and should be reviewed in pull requests.
- Versioned docs will matter for SaaS, single-school licensed releases, marketplace packages, and managed clients.
- Static hosting can be deployed on Sanfaani-controlled infrastructure.
- Markdown files in this repo can map cleanly into a Docusaurus docs tree later.

Mintlify is a strong alternative if the team prioritizes speed and hosted search over self-hosting control.
