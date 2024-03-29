<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Set;

/**
 * @api
 */
final class MigrationSetList
{
    /**
     * @var string
     */
    public const PHPSPEC_TO_PHPUNIT = __DIR__ . '/../../config/sets/phpspec-to-phpunit.php';

    /**
     * @var string
     */
    public const POST_RUN = __DIR__ . '/../../config/sets/post-run-set.php';
}
