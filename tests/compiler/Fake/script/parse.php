<?php
//
// Usage:
//
// php parse.php dependency.php
//

require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
$file = $argv[1];

$parser = new PhpParser\Parser(new PhpParser\Lexer);
try {
    $stmts = $parser->parse(file_get_contents(__DIR__ . "/{$file}"));
    var_dump($stmts);
} catch (PhpParser\Error $e) {
    echo 'Parse Error: ', $e->getMessage();
}
