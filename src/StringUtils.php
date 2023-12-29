<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

final class StringUtils
{
    /**
     * @param string[] $suffixesToRemove
     */
    public static function removeSuffixes(string $name, array $suffixesToRemove): string
    {
        foreach ($suffixesToRemove as $suffixToRemove) {
            if (str_ends_with($name, $suffixToRemove)) {
                $name = substr($name, 0, -strlen($suffixToRemove));
            }
        }

        return $name;
    }

    /**
     * @param string[] $prefixesToRemove
     */
    public static function removePrefixes(string $name, array $prefixesToRemove): string
    {
        foreach ($prefixesToRemove as $prefixToRemove) {
            if (str_starts_with($name, $prefixToRemove)) {
                $name = substr($name, strlen($prefixToRemove));
            }
        }

        return $name;
    }

    public static function underscoreAndHyphenToCamelCase(string $content): string
    {
        $content = str_replace(['-', '_'], ' ', $content);
        $content = ucwords($content);

        return str_replace(' ', '', $content);
    }

    /**
     * @license used from nette/utils https://github.com/nette/utils/blob/0d1508c344caea70a71467c3d25a6add9de613a0/src/Utils/Strings.php#L472
     */
    public static function after(string $haystack, string $needle, int $nth = 1): ?string
    {
        $position = self::pos($haystack, $needle, $nth);
        return $position === null ? null : \substr($haystack, $position + strlen($needle));
    }

    /**
     * @license used from nette/utils https://github.com/nette/utils/blob/0d1508c344caea70a71467c3d25a6add9de613a0/src/Utils/Strings.php#L494C2-L526C1
     */
    private static function pos(string $haystack, string $needle, int $nth = 1): ?int
    {
        if (! $nth) {
            return null;
        } elseif ($nth > 0) {
            if ($needle === '') {
                return 0;
            }

            $pos = 0;
            while (($pos = strpos($haystack, $needle, $pos)) !== false && --$nth) {
                $pos++;
            }
        } else {
            $len = strlen($haystack);
            if ($needle === '') {
                return $len;
            } elseif ($len === 0) {
                return null;
            }

            $pos = $len - 1;
            while (($pos = strrpos($haystack, $needle, $pos - $len)) !== false && ++$nth) {
                $pos--;
            }
        }

        if ($pos === false) {
            return null;
        }

        return $pos;
    }
}
