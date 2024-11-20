# Rector Rules for PhpSpec to PHPUnit migration

* [Rules Overview](/docs/rector_rules_overview.md)

## Install

* runs on PHP 7.4+

```bash
composer require rector/custom-phpspec-to-phpunit --dev
```

## Usage

1. Register set

```php
$rectorConfig->sets([
    \Rector\PhpSpecToPHPUnit\Set\MigrationSetList::PHPSPEC_TO_PHPUNIT,
]);
```

2. Run on specific spec directory

```bash
vendor/bin/rector process spec
```

<br>

This package handles 99 % of changes. Explore these resources to handle edge-cases manually:

* https://johannespichler.com/writing-custom-phpspec-matchers/
