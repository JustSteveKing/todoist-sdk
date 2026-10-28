# Changelog

Every notable change to this package. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the package follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html): until 1.0, a minor release may change a public API, and the entry will say so.

## [Unreleased]

### Added

- A client for Todoist's API v1 on sdk-toolbox, with Todoist's errors mapped to typed exceptions and its own details one call away.
- Tasks, projects, sections, labels (personal and shared), comments, uploads and the user, with cursor pagination and models checked against Todoist's OpenAPI spec.
- OAuth: the authorisation code flow with PKCE, a refresher that respects Todoist's rotating refresh tokens, revocation, and runtime client registration (RFC 7591).
- Webhook verification and parsing.
- `bin/spec`, and a weekly workflow, to catch drift from Todoist's published spec.

### Changed

- Requires sdk-toolbox `^0.0.2`. OAuth now uses the toolbox's `RefreshingToken` with `rotating: true`, its comma scope separator and its RFC 7591 registration, in place of the SDK's own copies. `refreshing()` returns a `RefreshingToken`, and `register()` a toolbox `RegisteredClient`.

[Unreleased]: https://github.com/JustSteveKing/todoist-sdk/commits/main
