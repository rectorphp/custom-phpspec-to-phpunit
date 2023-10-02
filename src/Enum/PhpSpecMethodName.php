<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Enum;

final class PhpSpecMethodName
{
    /**
     * @var string
     */
    public const LET = 'let';

    /**
     * @var string
     */
    public const LET_GO = 'letGo';

    /**
     * @var string
     */
    public const BE_CONSTRUCTED_THROUGH = 'beConstructedThrough';

    /**
     * @var string
     */
    public const GET_MATCHERS = 'getMatchers';

    /**
     * @var string
     */
    public const SHOULD_HAVE_TYPE = 'shouldHaveType';

    /**
     * @var string[]
     */
    public const RESERVED_CLASS_METHOD_NAMES = [self::GET_MATCHERS, self::LET, self::LET_GO];
}
