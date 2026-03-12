--TEST--
soda (no args - show commands)
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ASoda %s

Usage:
  command [options] [arguments]
%A
  analyse     Analyse PHP project size and collect metrics
%A
  init        Create soda.json with default quality rules
%A
  quality     Analyse code quality and check against configured thresholds
