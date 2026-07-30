# Graph Report - plugin-laravel  (2026-07-30)

## Corpus Check
- 17 files · ~3,220 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 119 nodes · 125 edges · 15 communities (12 shown, 3 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0d76ebd1`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- composer.json
- KrynoxCaptcha.php
- KrynoxServiceProvider.php
- MockPlane
- keywords
- KrynoxCaptcha
- Krynox Captcha for Laravel
- WidgetComponentTest
- extra
- require
- Changelog
- TestCase

## God Nodes (most connected - your core abstractions)
1. `TestCase` - 14 edges
2. `MockPlane` - 12 edges
3. `keywords` - 7 edges
4. `Krynox Captcha for Laravel` - 7 edges
5. `KrynoxCaptcha` - 6 edges
6. `WidgetComponentTest` - 5 edges
7. `require` - 4 edges
8. `support` - 4 edges
9. `KrynoxServiceProvider` - 4 edges
10. `Widget` - 4 edges

## Surprising Connections (you probably didn't know these)
- `FeedbackTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/FeedbackTest.php → tests/TestCase.php
- `TestCase` --references--> `MockPlane`  [EXTRACTED]
  tests/TestCase.php → tests/Support/MockPlane.php
- `VerifyRetryTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/VerifyRetryTest.php → tests/TestCase.php
- `WidgetComponentTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/WidgetComponentTest.php → tests/TestCase.php
- `RuleObjectTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/RuleObjectTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (15 total, 3 thin omitted)

### Community 0 - "composer.json"
Cohesion: 0.09
Nodes (22): authors, autoload, autoload-dev, psr-4, psr-4, description, homepage, license (+14 more)

### Community 1 - "KrynoxCaptcha.php"
Cohesion: 0.18
Nodes (5): Closure, Illuminate\Contracts\Validation\ValidationRule, Krynox, FeedbackTest, VerifyRetryTest

### Community 2 - "KrynoxServiceProvider.php"
Cohesion: 0.21
Nodes (5): Illuminate\Contracts\View\View, Illuminate\Support\ServiceProvider, Illuminate\View\Component, KrynoxServiceProvider, Widget

### Community 4 - "keywords"
Cohesion: 0.29
Nodes (7): keywords, captcha, krynox, laravel, privacy, proof-of-work, spam

### Community 6 - "Krynox Captcha for Laravel"
Cohesion: 0.22
Nodes (8): Config, Feedback (false-positive correction), Honeypot, Install, Krynox Captcha for Laravel, License, Render the widget, Verify the submission

### Community 8 - "extra"
Cohesion: 0.50
Nodes (4): extra, laravel, providers, Krynox\\Captcha\\KrynoxServiceProvider

### Community 9 - "require"
Cohesion: 0.50
Nodes (4): require, guzzlehttp/guzzle, illuminate/support, php

### Community 10 - "Changelog"
Cohesion: 0.40
Nodes (4): [0.1.0] - 2026-07-22, Added, Changelog, [Unreleased]

### Community 11 - "TestCase"
Cohesion: 0.13
Nodes (4): Orchestra\Testbench\TestCase, RuleObjectTest, TestCase, ValidatorRuleTest

## Knowledge Gaps
- **33 isolated node(s):** `name`, `description`, `laravel`, `captcha`, `krynox` (+28 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **3 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `TestCase` to `KrynoxCaptcha.php`, `KrynoxServiceProvider.php`, `MockPlane`, `WidgetComponentTest`?**
  _High betweenness centrality (0.214) - this node is a cross-community bridge._
- **Why does `MockPlane` connect `MockPlane` to `KrynoxServiceProvider.php`, `TestCase`?**
  _High betweenness centrality (0.084) - this node is a cross-community bridge._
- **Why does `VerifyRetryTest` connect `KrynoxCaptcha.php` to `TestCase`?**
  _High betweenness centrality (0.066) - this node is a cross-community bridge._
- **What connects `name`, `description`, `laravel` to the rest of the system?**
  _33 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.08695652173913043 - nodes in this community are weakly interconnected._
- **Should `TestCase` be split into smaller, more focused modules?**
  _Cohesion score 0.13333333333333333 - nodes in this community are weakly interconnected._