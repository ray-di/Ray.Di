<?php
namespace Ray\Di;

use Doctrine\Common\Cache\FilesystemCache;

require dirname(__DIR__) . '/bootstrap.php';

$injector = require dirname(dirname(__DIR__)) . '/scripts/instance.php';
$injector->setModule(new Modules\TimeModule)->setCache(new FilesystemCache(__DIR__ . '/tmp'));
$time = $injector->getInstance('Ray\Di\Mock\Time2');
echo $time->time;
exit;