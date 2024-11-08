<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8a5769060352133776cb0cdf9f6f6fda
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Almefy\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Almefy\\' => 
        array (
            0 => __DIR__ . '/..' . '/almefy/client/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8a5769060352133776cb0cdf9f6f6fda::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8a5769060352133776cb0cdf9f6f6fda::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8a5769060352133776cb0cdf9f6f6fda::$classMap;

        }, null, ClassLoader::class);
    }
}
