<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit250a31363f805069711558358520d4a2
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Finder\\' => 25,
        ),
        'R' => 
        array (
            'Rector\\PhpSpecToPHPUnit\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Finder\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/finder',
        ),
        'Rector\\PhpSpecToPHPUnit\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
            1 => __DIR__ . '/../..' . '/rules',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit250a31363f805069711558358520d4a2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit250a31363f805069711558358520d4a2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit250a31363f805069711558358520d4a2::$classMap;

        }, null, ClassLoader::class);
    }
}
