# Rector Rules for PhpSpec to PHPUnit migration

* [Rules Overview](/docs/rector_rules_overview.md)

## Install

* runs on PHP 7.2+

```bash
composer require rector/custom-phpspec-to-phpunit --dev
```

## Register set

```php
$rectorConfig->sets([
    \Rector\PhpSpecToPHPUnit\Set\MigrationSetList::PHPSPEC_TO_PHPUNIT,
]);
```

Other resources to handle rest of changes manually:

* https://johannespichler.com/writing-custom-phpspec-matchers/
