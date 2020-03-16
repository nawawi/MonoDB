<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitab83e689f05eef1be66e4da087ba1a9b
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\VarExporter\\' => 30,
        ),
        'M' => 
        array (
            'MonoDB\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\VarExporter\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/var-exporter',
        ),
        'MonoDB\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitab83e689f05eef1be66e4da087ba1a9b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitab83e689f05eef1be66e4da087ba1a9b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
