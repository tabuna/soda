--TEST--
soda ../_fixture
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda', 'analyse', 'does-not-exist'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ANo files found to scan
