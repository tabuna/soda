--TEST--
soda quality
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda', 'quality', __DIR__ . '/../quality-fixture'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ACode Quality Report
──────────────────
Score: 100 / 100

Summary
───────

Violations: 0
Score: 100
