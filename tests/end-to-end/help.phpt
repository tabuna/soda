--TEST--
soda analyse --help
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda', 'analyse', '--help'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ADescription:
  Analyse PHP project size and collect metrics

Usage:
  analyse [options] [--] [<path>...]

Arguments:
%Apath%A

Options:
%A--report-json%A
