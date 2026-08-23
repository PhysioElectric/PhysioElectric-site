<?php
declare(strict_types=1);

/**
 * Exits 0 when the database TCP port is reachable, 1 otherwise.
 * Used by entrypoint.sh to wait for MySQL.
 */
$host = getenv('DB_HOST') ?: 'db';
$port = (int) (getenv('DB_PORT') ?: 3306);
$err  = 0;
$errno = 0;
$errstr = '';
$c = @fsockopen($host, $port, $errno, $errstr, 3);
exit($c ? 0 : 1);
