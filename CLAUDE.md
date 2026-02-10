# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP library that adapts Swagger 2.0 JSON schemas into `Components` (Models and Enums) consumed by `zero-to-prod/data-model-generator`. Part of the zero-to-prod ecosystem.

**Namespace:** `Zerotoprod\DataModelAdapterSwagger\`
**PHP:** >= 8.1

## Commands

All commands use the `dock` helper script which wraps Docker Compose. Requires `.env` file (run `sh dock init` to create from `.env.example`). The `.env` `PHP_VERSION` variable controls which PHP version (8.1–8.5) is used.

```bash
sh dock init                        # Create .env from .env.example
sh dock composer update             # Install/update dependencies
sh dock test                        # Run all tests
sh dock test --filter=ClassName     # Run a single test class
sh dock test --filter=methodName    # Run a single test method
sh test.sh                          # Run tests across all PHP versions
```

## Architecture

**`src/Swagger.php`** — Single entry point. `Swagger::adapt(array $swagger_schema): Components` parses a Swagger 2.0 schema and produces Models and Enums. The method iterates `definitions`, skipping simple string/array-ref types, and builds:
- **Models**: class constants (property name strings with `@see` docblocks), typed properties with attributes for complex casting (e.g., `mapOf` via `DataModelHelper`), and `required` status from schema
- **Enums**: backed string enums from schema `enum` arrays

**`src/Resolvers/PropertyTypeResolver.php`** — Resolves Swagger schema types to PHP types, handling `$ref`, `oneOf`, and `anyOf`.

**`src/Helpers/DataModel.php`** — Trait composing `DataModel`, `Transformable`, and `DataModelHelper`.

## Test Patterns

Tests live in `tests/` with PHPUnit 10. Base class `Tests\TestCase` manages a `./tests/generated` output directory (cleaned before each test).

**Acceptance test pattern** (most tests follow this):
1. Place a `schema.json` alongside the test file
2. Call `Swagger::adapt()` on the parsed JSON
3. Pass result to `Engine::generate()` with a config pointing to `self::$test_dir`
4. Assert generated PHP file contents with `assertStringEqualsFile()`

Test directories are organized by feature: `Class/`, `Properties/`, `Constants/`, `Spapi/`.