--TEST--
soda init
--FILE--
<?php declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);
require __DIR__ . '/../../vendor/autoload.php';

$tmp = __DIR__ . '/init-temp-' . uniqid();
mkdir($tmp, 0700, true);
$cwd = getcwd();
chdir($tmp);

$_SERVER['argv'] = ['soda', 'init'];

define('SODA_ENTRY_NO_EXIT', true);

require __DIR__ . '/../../soda';

chdir($cwd);
if (file_exists($tmp . '/soda.json')) {
    unlink($tmp . '/soda.json');
}
if (is_dir($tmp)) {
    rmdir($tmp);
}
--EXPECTF--
Created soda.json
