# Security

## Supported versions

The SDK is at 0.x. Security fixes go into the latest release only.

| Version | Supported |
|---|---|
| 0.0.x (latest) | yes |

## Reporting a vulnerability

Please don't open a public issue. Report it privately, either through GitHub's [private vulnerability reporting](https://github.com/JustSteveKing/todoist-sdk/security/advisories/new) or by email to steve@juststeveking.com.

Include the version, a description of the problem, and if you can, the smallest code that reproduces it. Once there's a fix, it's released with a GitHub security advisory crediting you, unless you'd rather not be named.

A problem in Todoist's API itself isn't something this project can fix, so report that to Todoist directly.

## What the SDK protects

- Your API token, OAuth access and refresh tokens, and client secret never appear in exception messages, logs or dumps. sdk-toolbox redacts them, and the SDK's own classes holding secrets define `__debugInfo()`.
- `User` leaves out the fields where Todoist returns the user's own API token.
- OAuth always uses PKCE and checks `state` in constant time. Refresh tokens are never sent again once Todoist has rotated them, so a lost reply can't trigger Todoist's replay protection.
- Webhook signatures are compared in constant time, over the raw body.
- Task, project and other IDs are encoded before they go into a path, so an ID can't reach another endpoint.

Any way around those is a vulnerability.
