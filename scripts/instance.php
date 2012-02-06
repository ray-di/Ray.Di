<?php
namespace Ray\Di;

require_once dirname(__DIR__) . '/src.php';
require_once dirname(__DIR__) . '/vendor/Ray.Aop/src.php';
return new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))));