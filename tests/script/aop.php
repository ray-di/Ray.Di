<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Di;

require dirname(__DIR__) . '/bootstrap.php';

$injector = new Injector(new FakeAopModule, $_ENV['TMP_DIR']);
file_put_contents(__FILE__ . '.cache', serialize($injector));
