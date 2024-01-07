<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitba29c543c2e2c8c5158c2d2933721790
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitba29c543c2e2c8c5158c2d2933721790::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitba29c543c2e2c8c5158c2d2933721790::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitba29c543c2e2c8c5158c2d2933721790::$classMap;

        }, null, ClassLoader::class);
    }
}
