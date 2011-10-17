<?php
namespace Ray\Di;

require_once dirname(__DIR__) . '/src.php';
require_once __DIR__ . '/EmptyModule.php';
return new Injector(new Container(new Forge(new Config(new Annotation))), new EmptyModule);