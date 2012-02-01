<?php
namespace Ray\Di;

require_once dirname(dirname(dirname(__DIR__))) . '/src.php';
//require_once dirname(dirname(dirname(__DIR__))) . '/vendor/Ray.Aop/src.php';
$injector = new Injector(new Container(new Forge(new Config(new Annotation))), new EmptyModule);
return $injector;