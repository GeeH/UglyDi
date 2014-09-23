<?php

$loader = @include(realpath('./../../vendor/autoload.php'));

if (!$loader && !($loader = @include 'vendor/autoload.php')) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

$loader->add('UglyDi\\', 'src');
$loader->add('UglyDiTest\\', 'tests');