<?php

namespace Ray\Di;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Aop\Compiler;

require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/Mock/Diary/diary_classes.php';
$tmpDir = __DIR__ . '/cache';
$cache = new FilesystemCache($tmpDir);

$moduleProvider = function () {
    return new DiaryAopModule;
};
$cache->setNamespace('diary');
$injector = DiCompiler::create($moduleProvider, $cache, 'diary', $tmpDir);

return $injector;
