--TEST--
soda --exclude ../_fixture/example_function.php ../_fixture
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = [
    'soda',
    'analyse',
    '--exclude', __DIR__ . '/../_fixture/example_function.php',
    __DIR__ . '/../_fixture',
];

require __DIR__ . '/../../soda';
--EXPECTF--
%ADirectories:                                                            1
Files:                                                                  3
%ASize
  Lines of Code (LOC)                                                 118
  Comment Lines of Code (CLOC)                                         24 (20.34%)
  Non-Comment Lines of Code (NCLOC)                                    94 (79.66%)
  Logical Lines of Code (LLOC)                                         27 (22.88%)
%ACyclomatic Complexity%ADependencies%AStructure
  Namespaces                                                             1
  Interfaces                                                             1
  Traits                                                                 1
  Classes                                                                1
    Abstract Classes                                                     1 (100.00%)
%A
