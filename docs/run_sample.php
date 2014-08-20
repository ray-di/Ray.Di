<?php
/**
 * Run all example
 *
 * $ php runall.php
 */
$list = [
    'sample/01-linked-binding.php',
    'sample/02-provider-binding.php',
    'sample/03-named-binding.php',
    'sample/04-toConstructor-binding.php',
    'sample/05-object-cycle.php',
    'sample/06-aop.php',
    'zf2-di-tests-clone/01-runtime-constructor-injection.php',
    'zf2-di-tests-clone/01A-runtime-constructor-injection-lazy.php',
    'zf2-di-tests-clone/03-runtime-setter-injection.php',
    'zf2-di-tests-clone/04-constructor-injection-with-config-params.php',
    'zf2-di-tests-clone/05-constructor-injection-with-calltime-params.php',
    'zf2-di-tests-clone/09-runtime-setter-injection-with-annotation.php',
    'zf2-di-tests-clone/09A-runtime-setter-injection-with-annotation-interface.php',
    'zf2-di-tests-clone/12-setter-injection-with-annotation.php',
    'zf2-di-tests-clone/12A-setter-injection-with-annotation-interface.php',
    'db-app/original.php',
    'db-app/main.php'
];

$result = 0;
foreach ($list as $php) {
    echo "=====================" . PHP_EOL;
    echo $php . PHP_EOL;
    echo "=====================" . PHP_EOL;
    $file = __DIR__ . "/{$php}";
    passthru("php $file");
    echo PHP_EOL;
}
echo "\nComplete" . PHP_EOL;
