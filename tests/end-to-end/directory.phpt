--TEST--
soda ../_fixture
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$_SERVER['argv'] = ['soda', 'analyse', __DIR__ . '/../_fixture'];

require __DIR__ . '/../../soda';
--EXPECTF--
%ADirectories:                                                            1
Files:                                                                  4
%ASize
  Lines of Code (LOC)                                                 164
  Comment Lines of Code (CLOC)                                         32 (19.51%)
  Non-Comment Lines of Code (NCLOC)                                   132 (80.49%)
  Logical Lines of Code (LLOC)                                         40 (24.39%)
%ACyclomatic Complexity%ADependencies%AStructure
  Namespaces                                                             1
  Interfaces                                                             1
  Traits                                                                 1
  Classes                                                                1
    Abstract Classes                                                     1 (100.00%)
%A
