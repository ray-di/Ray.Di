<?php

namespace Ray\Di;

require __DIR__ . '/cache_compiler.php';

/** @var $injector DiCompiler */
$instance = $injector->getInstance('Ray\Di\WriterInterface');
$result = ($instance instanceof \Ray\Di\Writer) ? 'works' : 'failure';

echo $result;
