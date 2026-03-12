--TEST--
soda --suffix .lib ../_fixture/example_function.php ../_fixture
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = [
    'soda',
    'analyse',
    '--suffix', '.lib',
    __DIR__ . '/../_fixture/example_function.php',
    __DIR__ . '/../_fixture',
];

require __DIR__ . '/../../soda';
--EXPECTF--
%ADirectories:                                                            1
Files:                                                                  5
%ASize
  Lines of Code (LOC)                                                 207
  Comment Lines of Code (CLOC)                                         40 (19.32%)
  Non-Comment Lines of Code (NCLOC)                                   167 (80.68%)
  Logical Lines of Code (LLOC)                                         53 (25.60%)
%ACyclomatic Complexity%ADependencies%AStructure
  Namespaces                                                             1
  Interfaces                                                             1
  Traits                                                                 1
  Classes                                                                1
    Abstract Classes                                                     1 (100.00%)
%A
