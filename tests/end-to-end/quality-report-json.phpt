--TEST--
soda quality --report-json
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$reportPath = sys_get_temp_dir() . '/soda-quality-' . uniqid() . '.json';
$_SERVER['argv'] = ['soda', 'quality', '--report-json', $reportPath, __DIR__ . '/../quality-fixture'];

require __DIR__ . '/../../soda';

$json = file_get_contents($reportPath);
unlink($reportPath);
$data = json_decode($json, true);

if (! is_array($data)) {
    echo "Invalid JSON\n";
    exit(1);
}
if (! isset($data['score'], $data['metrics'], $data['violations'])) {
    echo "Missing required keys\n";
    exit(1);
}
echo "OK\n";
--EXPECTF--
%A
OK
