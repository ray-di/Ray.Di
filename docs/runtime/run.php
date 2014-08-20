<?php
/**
 * Performance test
 *
 * $ php run.php
 *
 */
$list = [
    '00-no-cache.php',
    '01-cache-module.php',
    '02-cache-injector.php',
    '03-di-compiler.php'
];

$result = 0;
foreach ($list as $php) {
    $t = microtime(true);
    echo $php . PHP_EOL;
    $file = __DIR__ . "/{$php}";
    passthru("php $file");
    echo microtime(true) - $t . PHP_EOL . PHP_EOL;
}
echo "Complete" . PHP_EOL;
