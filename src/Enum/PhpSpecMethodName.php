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
    public const EXPECT_EXCEPTION = 'expectException';

    /**
     * @var string
     */
    public const SHOULD_HAVE_TYPE = 'shouldHaveType';

    /**
     * @var string[]
     */
    public const RESERVED_CLASS_METHOD_NAMES = [self::GET_MATCHERS, self::LET, self::LET_GO];

    /**
     * @var string
     */
    public const SHOULD_BE_CALLED = 'shouldBeCalled';

    /**
     * @var string
     */
    public const GET_WRAPPED_OBJECT = 'getWrappedObject';

    /**
     * @var string
     */
    public const DURING = 'during';

    /**
     * @var string
     */
    public const SHOULD_THROW = 'shouldThrow';

    /**
     * @var string
     */
    public const DURING_INSTANTIATION = 'duringInstantiation';

    /**
     * @var string
     */
    public const SHOULD_NOT_BE_CALLED = 'shouldNotBeCalled';

    /**
     * @var string
     */
    public const BE_CONSTRUCTED_WITH = 'beConstructedWith';

    /**
     * @var string
     */
    public const BE_CONSTRUCTED = 'beConstructed';

    /**
     * @var string
     */
    public const CLONE = 'clone';

    /**
     * @var string
     */
    public const WILL_RETURN = 'willReturn';

    /**
     * @var string
     */
    public const WITH = 'with';

    /**
     * @var string
     */
    public const SHOULD_NOT_THROW = 'shouldNotThrow';

    /**
     * @var string
     */
    public const BE_AN_INSTANCE_OF = 'beAnInstanceOf';

    /**
     * @var string
     */
    public const SHOULD_RETURN = 'shouldReturn';
}
