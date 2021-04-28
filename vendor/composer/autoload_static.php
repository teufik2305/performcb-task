<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit64038575f88f99e8048de7186d2812ec
{
    public static $prefixLengthsPsr4 = array (
        'a' => 
        array (
            'app\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit64038575f88f99e8048de7186d2812ec::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit64038575f88f99e8048de7186d2812ec::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit64038575f88f99e8048de7186d2812ec::$classMap;

        }, null, ClassLoader::class);
    }
}
