--TEST--
soda --version
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda', '--version'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ASoda %s
