<?php
namespace Ray\Di;

use Doctrine\Common\Cache\FilesystemCache;

require dirname(__DIR__) . '/bootstrap.php';

$injector = require $ENV['PACKAGE_DIR'] . '/scripts/instance.php';
$injector->setModule(new Modules\TimeModule)->setCache(new FilesystemCache(__DIR__ . '/tmp'));
$time = $injector->getInstance('Ray\Di\Mock\Time2');
echo $time->time;
exit;
