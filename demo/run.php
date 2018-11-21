<?php

declare(strict_types=1);

passthru('php ' . __DIR__ . '/01a-linked-binding.php');
passthru('php ' . __DIR__ . '/01b-linked-binding-setter-injection.php');
passthru('php ' . __DIR__ . '/02-provider-binding.php');
passthru('php ' . __DIR__ . '/02a-named-by-qualifier.php');
passthru('php ' . __DIR__ . '/02b-named-by-named.php');
passthru('php ' . __DIR__ . '/03-injection-point.php');
passthru('php ' . __DIR__ . '/04-untarget-binding.php');
passthru('php ' . __DIR__ . '/05a-constructor-binding.php');
passthru('php ' . __DIR__ . '/05b-constructor-binding-setter-injection.php');
passthru('php ' . __DIR__ . '/07-assisted-injection.php');
passthru('php ' . __DIR__ . '/10-cache.php');
passthru('php ' . __DIR__ . '/11-script-injector.php');
//passthru('php ' . __DIR__ . '/12-dependency-chain-error-message.php');
