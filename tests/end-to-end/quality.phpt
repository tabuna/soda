--TEST--
soda quality
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda', 'quality', __DIR__ . '/../quality-fixture'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ASoda Quality
------------------------------------------------------------

[OK] No issues
