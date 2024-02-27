<?php

namespace TaskService\Config;

/**
 * application config for local dev environment and ci
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Config
{
    public string $mysqlHost = 'mysql';

    public string $mysqlUsername = 'root';

    public string $mysqlPassword = 'root';

    public string $mysqlDatabase = 'tasks';

    public string $clickhouseHost = 'clickhouse';

    public string $clickhouseUsername = 'root';

    public string $clickhousePassword = 'root';

    public string $clickhouseDatabase = 'tasks';

    public string $redisHost = 'redis';

    public int $redisPort = 6379;

    public string $redisUsername = 'default';

    public string $redisPassword = 'default';

    public string $redisStreamTasks = 'tasks';

    public string $redisStreamGroup = 'mygroup';

    public string $publicKey = '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAoCOg744fgP2MAf8BcoHg
11Ldd9YlAOT14qvFvtlyxXdR57SYwVkkfZCPvJ9ctj1wa9xUmF+/ClPJYFb5Juzj
CiITU+xXhC9ONXnKH2ve1UF48fCi8K01JkvDrsZOuxrAVDpzhBF1Cq1iHhOD3bw+
uSjQlqOzXMIOBAUBnOSOpuNYPcmupPnwCw6V+exBnIhsilBTBkoIW3pFlEz4WQtf
SnW3goGVqws9JK4IixbztqSLypjiQZrNzq0IrmRfUvw7FOX25SoXUzrA2zf1jzv4
eH8PxXQm/wd6zm7O3eedWN2OVWMpBhtGnZJSgANmzRxKOGJziUoecWRo5OT8qoXY
WQWAAgy3YRGdIiF14ewvXuXaPV2GMeMXVUz9yapw7sdvH5U3pmUr7Z1EMZqZEXPD
wIO3MF6Vj9XHsL/BvYsP6N8N6bMbLUHB4I1jmHY8u3gG3B+YMZs9d4gR4nUSttia
/PFDQsvJziwGBOptDKbkojgwk3oXnpqZj4Z7tSD99urCs/BhnmiQNbmgJEhbWAiK
jXTxQp3pKS4twqXckLhU9yPTAHqkEluLVQ188cUOGt0Tk/nxiySJRF2m00Virbxr
6dM3c/3djigMTZLqQZInOF4vOOg/S7+CmnFFyV5Xn69o8EySLPfUp3pppVuVhuKa
ozcwKcBR88PnfjyPyCC5chcCAwEAAQ==
-----END PUBLIC KEY-----';

    // example private key only for local usage
    public string $privateKey = '-----BEGIN RSA PRI' . 'VATE KEY-----
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
-----END RSA PRIVATE KEY-----';

    public string $logfile = '/var/log/php';
}
