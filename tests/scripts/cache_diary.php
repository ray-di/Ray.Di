<?php

namespace Ray\Di;

require __DIR__ . '/cache_compiler.php';

/** @var $injector DiCompiler */
$instance = $injector->getInstance('Ray\Di\DiaryInterface');
$result = ($instance instanceof \Ray\Di\Diary) ? 'works' : 'failure';

echo $result;
