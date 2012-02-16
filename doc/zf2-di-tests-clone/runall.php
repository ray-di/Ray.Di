<?php
/**
 * Run all example
 *
 * $ php runall.php
 */
$list = [
    '01-runtime-constructor-injection.php',
    '01A-runtime-constructor-injection-lazy.php',
    '03-runtime-setter-injection.php',
    '04-constructor-injection-with-config-params.php',
    '05-constructor-injection-with-calltime-params.php',
    '09-runtime-setter-injection-with-annotation.php',
    '09A-runtime-setter-injection-with-annotation-interface.php',
    '12-setter-injection-with-annotation.php',
    '12A-setter-injection-with-annotation-interface.php',
];

foreach ($list as $run) {
    echo "$run: ";
    passthru("php $run");
    echo "\n";
}
echo "\nComplete.\n";
