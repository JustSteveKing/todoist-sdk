# Contributing

Thanks for wanting to help. Bug reports, fixes and the endpoints the SDK doesn't cover yet are all welcome.

## Before you start

- **Bugs:** open an issue with the bug report form. A failing test, or the smallest code that shows the problem, is the most useful thing you can include. Leave out your token and anyone's personal data.
- **New endpoints:** open an issue first, naming the endpoint, so we can agree how it should be called.
- **Security problems:** please don't open a public issue. See [SECURITY.md](SECURITY.md).

This project isn't run by Todoist. Questions about what the API itself does belong with [Todoist's documentation](https://developer.todoist.com/api/v1/).

## How the SDK is built

[AGENTS.md](AGENTS.md) holds the rules in full. It's written for AI agents, but it's the same rulebook for people. The short version:

- **Build from Todoist's spec, not from memory.** `spec/todoist-openapi.json` is Todoist's own OpenAPI spec. Models, inputs and fixtures come from it, and the contract tests fail when the SDK and the spec disagree.
- **sdk-toolbox does the HTTP.** Requests are small classes; retries, pagination, redaction and hydration come from the toolbox. If the toolbox can't do something Todoist needs, say so in the pull request.
- **PHP 8.3 is the floor.** CI runs 8.3, 8.4 and 8.5.
- **Secrets never escape.** Tokens and secrets carry `#[SensitiveParameter]`, and classes that hold them define `__debugInfo()`.

## Working on it

```bash
composer install
composer test        # Pest
composer analyse     # PHPStan, at max, no baseline
composer lint        # Pint (vendor/bin/pint to fix)
composer spec:check  # compare spec/ with what Todoist publishes
```

The first three have to pass.

## Pull requests

- Keep each one to a single change, with a description of what changed and why.
- Add or update tests. A test that passes first time is worth breaking on purpose once, to prove it can fail.
- Add a line to [CHANGELOG.md](CHANGELOG.md) under "Unreleased".

By contributing, you agree that your work is licensed under the project's [MIT licence](LICENSE.md), and to follow the [code of conduct](CODE_OF_CONDUCT.md).
