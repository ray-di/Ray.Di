<?php

declare(strict_types=1);

use function Ray\Compiler\deleteFiles;

require_once dirname(__DIR__) . '/vendor/autoload.php';

deleteFiles(__DIR__ . '/tmp');
deleteFiles(__DIR__ . '/compiler/tmp');
