<?php

spl_autoload_register(function ($class) {
    $prefixMap = [
        'Phinx\\' => __DIR__ . '/robmorgan/phinx-0.x/src/Phinx/',
        'Symfony\\Component\\Console\\' => __DIR__ . '/symfony/console/',
        'Symfony\\Component\\Yaml\\' => __DIR__ . '/symfony/yaml/',
        // Add other Symfony components here if needed
    ];

    foreach ($prefixMap as $prefix => $baseDir) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});