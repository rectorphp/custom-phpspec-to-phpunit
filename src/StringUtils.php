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
            if (substr_compare($name, $suffixToRemove, -strlen($suffixToRemove)) === 0) {
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
            if (strncmp($name, $prefixToRemove, strlen($prefixToRemove)) === 0) {
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
}
