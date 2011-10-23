<?php
namespace Ray\Di\Sample;

$injector = include dirname(dirname(__DIR__)) . '/scripts/instance.php';

require __DIR__ . '/User.php';
require __DIR__ . '/UserModule.php';
require __DIR__ . '/Transaction.php';
require __DIR__ . '/Timer.php';
require __DIR__ . '/Template.php';

$injector->setModule(new UserModule);
$user = $injector->getInstance('Ray\Di\Sample\User');
/* @var $user \Ray\Di\Sample\User */
$user->createUser('Koriym', rand(18,35));
$user->createUser('Bear', rand(18,35));
$user->createUser('Yoshi', rand(18,35));
$users = $user->readUsers();
var_export($users);

// Timer start
// begin Transaction["Koriym",19]
// commit
// Timer stop:[0.0002480] sec

// Timer start
// begin Transaction["Bear",28]
// commit
// Timer stop:[0.0001690] sec

// Timer start
// begin Transaction["Yoshi",18]
// commit
// Timer stop:[0.0001669] sec

// Name:Koriym	Age:19
// Name:Bear	Age:28
// Name:Yoshi	Age:18