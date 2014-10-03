<?php
return function (UglyDi\UglyDi $di) {
    $noConstructor = $di->get('UglyDiTest\Asset\NoConstructor');
    return new UglyDiTest\Asset\OneConstructor($noConstructor);
};
