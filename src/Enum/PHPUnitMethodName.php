<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Enum;

final class PHPUnitMethodName
{
    /**
     * @var string
     */
    public const ONCE = 'once';

    /**
     * @var string
     */
    public const EXPECTS = 'expects';

    /**
     * @var string
     */
    public const WITH = 'with';

    /**
     * @var string
     */
    public const METHOD_ = 'method';

    /**
     * @var string
     */
    public const NEVER = 'never';
}
