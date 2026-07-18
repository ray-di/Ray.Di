# Ray.Di

## A dependency injection framework for PHP

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Di/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/ray-di/Ray.Di/?branch=2.x)
[![codecov](https://codecov.io/gh/ray-di/Ray.Di/branch/2.x/graph/badge.svg?token=KCQXtu01zc)](https://codecov.io/gh/ray-di/Ray.Di)
[![Type Coverage](https://shepherd.dev/github/ray-di/Ray.Di/coverage.svg)](https://shepherd.dev/github/ray-di/Ray.Di)
[![Continuous Integration](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml/badge.svg?branch=2.x)](https://github.com/ray-di/Ray.Di/actions/workflows/continuous-integration.yml)
[![Total Downloads](https://poser.pugx.org/ray/di/downloads)](https://packagist.org/packages/ray/di)

<img src="https://ray-di.github.io/images/logo.svg" width=160  alt="logo">

Ray.Di is DI and AOP framework for PHP inspired by [Google Guice](https://github.com/google/guice/wiki).

## Binding snapshots

Collect a module's composed bindings explicitly when diagnostics are needed:

```php
use Ray\Bindings\Bindings;

$bindings = new Bindings();
$module->accept($bindings);

$markdown = $bindings->toMarkdown();
$html = $bindings->toHtml($composerLock, 'prod-app', $vendorDir);
```

The snapshot is captured when `accept()` is called. Later module changes and
Injector processing do not change it; visiting another module with the same
`Bindings` instance replaces the snapshot. It contains bindings composed by
the application module through `bind()`, `install()`, and `override()`, without
Injector built-ins or bindings discovered later during object resolution.

`Injector` does not create `bindings.md` automatically. Applications that
previously read that generated file should use `toMarkdown()` or `toHtml()` as
shown above. The explicit `Ray\Bindings\BindingsMarkdown` file writer and the
`bindings-html` command remain available for file-based workflows.

Technical comparison with other PHP DI containers (Japanese): [docs/comparison.ja.md](docs/comparison.ja.md)

https://ray-di.github.io
