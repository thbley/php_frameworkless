<?php

error_reporting(E_ALL);

if (!file_exists('/tmp/coverage/index.xml')) {
    return;
}

$xml = simplexml_load_file('/tmp/coverage/index.xml');

/**
 * @var array{
 *   project: array{
 *     directory: array{
 *       '@attributes': array{ name: string },
 *       totals: array{
 *         lines: array{ '@attributes': array{ executable: int, executed: int, percent: float } },
 *         methods: array{ '@attributes': array{ count: int, tested: int, percent: float } },
 *         classes: array{ '@attributes': array{ count: int, tested: int, percent: float } }
 *       },
 *       directory: array{
 *         '@attributes': array{ name: string },
 *         totals: array{
 *           lines: array{ '@attributes': array{ executable: int, executed: int, percent: float } },
 *           methods: array{ '@attributes': array{ count: int, tested: int, percent: float } },
 *           classes: array{ '@attributes': array{ count: int, tested: int, percent: float } }
 *         }
 *       }[]
 *     }
 *   }
 * } $data
 */
$data = json_decode(json_encode($xml) ?: '', true);
$root = $data['project']['directory'];
$directories = $data['project']['directory']['directory'];

array_unshift($directories, $root);

$result = [];
foreach ($directories as $directory) {
    $lines = $directory['totals']['lines']['@attributes'];
    $methods = $directory['totals']['methods']['@attributes'];
    $classes = $directory['totals']['classes']['@attributes'];

    $result[] = sprintf(
        '# %-20s %3s / %3s   %6.2f     %3s / %3s   %6.2f     %2s / %2s   %6.2f',
        $directory['@attributes']['name'],
        $lines['executable'], $lines['executed'], $lines['percent'],
        $methods['count'], $methods['tested'], $methods['percent'],
        $classes['count'], $classes['tested'], $classes['percent']
    );
}

echo sprintf('# %26s %24s %22s', 'lines', 'methods', 'classes') . PHP_EOL;
echo implode(PHP_EOL, $result) . PHP_EOL;
