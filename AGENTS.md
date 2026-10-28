# AGENTS.md

A community PHP SDK for Todoist's API v1, built on [sdk-toolbox](https://github.com/JustSteveKing/sdk-toolbox) as its showcase. Read [README.md](README.md) before changing anything; it describes what the SDK promises.

## The rule that matters

**Build from Todoist's spec, never from memory.** `spec/todoist-openapi.json` is the OpenAPI spec Todoist embeds in its Redoc page at developer.todoist.com/api/v1. Take field names, types, required fields, request bodies and examples from it. Where the spec's guide text (in its `tags[].description`) documents something the schemas don't, such as OAuth or webhooks, read that, and say in the docblock where the behaviour comes from. Where the spec and the live API disagree, the live API wins, and the test that covers it says when it was checked.

`php bin/spec check` compares the copy with what Todoist publishes now; `php bin/spec update` replaces it. A weekly workflow runs the check.

## Todoist's terms

- "Todoist" can't be the primary name: the human-facing name is "PHP SDK for Todoist".
- The disclaimer "Not created by, affiliated with, or supported by Todoist" stays in the README, the composer description and the client's docblock.

## Layout

- `src/Todoist.php`: the connector. One accessor per resource.
- `src/Resources/`: one class per resource, a method per operation.
- `src/Requests/<Resource>/`: one class per endpoint, implementing sdk-toolbox's `Request<T>`. IDs go into paths through the resource's `*Request::path()`, which encodes them.
- `src/Data/`: response models, mapped by Valinor in strict mode. Property names are the spec's, in camelCase. A field the spec doesn't require gets a default.
- `src/Inputs/`: what you send. `New*` classes for creates; `*Changes` builders for updates, because Todoist reads `null` as "clear this" on some fields and a builder can tell "leave it" from "clear it".
- `src/OAuth/` and `src/Webhooks/`: sign-in and webhook verification.
- `src/Errors/`: Todoist's error shape mapped onto sdk-toolbox's status exceptions.

## Boundaries

- **PHP 8.3 is the floor.** No 8.4 or 8.5 functions or syntax.
- **Immutable values.** Data, inputs and resources are `final readonly`; `tests/Arch` checks it.
- **No `mixed` in models.** Valinor's strict mode refuses it. An untyped object in the spec gets its own small class, or is left out of the model with the reason in its docblock and the contract test.
- **Secrets never escape.** Tokens and secrets carry `#[SensitiveParameter]`, and classes holding them define `__debugInfo()`. Fields that carry a credential (the user's own API token in `/user`) are left out of models.
- **Unknown values don't break reads.** A colour, status or event Todoist adds later must not fail hydration, so models keep those as strings; enums are for inputs.

## Tests

- **Fixtures come from the spec's own examples.** `tests/Fixtures/*.json` are built from the schemas' `examples`; extend them with `example('name', [...overrides])`.
- **Contract tests** in `tests/Unit/SpecContractTest.php` hold every model's constructor to its schema's properties, every input to its request body, and the enums to the documented lists. A new model or input gets a row there.
- **A test that passes first time gets broken on purpose,** to prove it can fail. Back the file up with a copy, not `git checkout`.
- **Nothing calls Todoist.** Tests use sdk-toolbox's `FakeClient` and `FakeTime`.

## Checks

```bash
composer test       # Pest
composer analyse    # PHPStan max, no baseline
composer lint       # Pint (vendor/bin/pint to fix)
```

All three must pass.

## Writing

British spelling in prose. Plain, specific, no em dashes. Docblocks explain why, and name Todoist's behaviour where it's the reason.

## Commits

No attribution lines: no co-author trailers, no generated-by banners. One commit per piece of work, and its message says what changed and why.
