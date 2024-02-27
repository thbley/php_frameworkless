<?php

namespace TaskService\cli;

use Exception;

error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    throw new Exception('invalid interface');
}

$phpSocket = fsockopen('php', 9000);
if ($phpSocket === false) {
    throw new Exception('connection error');
}

// fcgi GET /status HTTP/1.1
/** @var string $packet */
$packet = json_decode('"\u0001\u0001\u0000\u0000\u0000\b\u0000\u0000\u0000\u0001\u0000\u0000\u0000\u0000\u0000\u0000' .
    '\u0001\u0004\u0000\u0000\u0000?\u0001\u0000\u000f\u0007SCRIPT_FILENAME\/status\u000b\u0007SCRIPT_NAME\/status' .
    '\u000e\u0003REQUEST_METHODGET\u0000\u0001\u0004\u0000\u0000\u0000\u0000\u0000\u0000\u0001\u0005\u0000\u0000' .
    '\u0000\u0000\u0000\u0000"', false, 10, 0);

fwrite($phpSocket, $packet);
echo fread($phpSocket, 4096);
fclose($phpSocket);
